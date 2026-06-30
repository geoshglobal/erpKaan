<?php

namespace App\Libraries;

use App\Models\CondominioModel;

/**
 * Tenant context for the Pool multi-tenant model.
 *
 * Resolves which condominios the current user may access and which one is
 * currently active (kept in session). All tenant-scoped screens should read
 * the active condominio from here so isolation stays centralized.
 */
class Tenant
{
    private const SESSION_KEY = 'active_condominio_id';

    /** @var array<int, array{id:int, nombre:string}>|null */
    private ?array $allowedCache = null;

    /**
     * Condominios the current user is allowed to access.
     *
     * @return array<int, array{id:int, nombre:string}>
     */
    public function allowedCondominios(): array
    {
        if ($this->allowedCache !== null) {
            return $this->allowedCache;
        }

        $model = new CondominioModel();
        $user  = auth()->user();

        if ($user === null) {
            return $this->allowedCache = [];
        }

        // Platform operator sees every active condominio.
        if ($user->inGroup('superadmin')) {
            return $this->allowedCache = $model
                ->select('id, nombre')
                ->where('activo', 1)
                ->orderBy('nombre', 'ASC')
                ->findAll();
        }

        // Otherwise: condominios granted as staff (condominio_usuarios)
        // plus the one tied to the user's persona record.
        $db  = db_connect();
        $ids = [];

        $staff = $db->table('condominio_usuarios')
            ->select('condominio_id')
            ->where('user_id', $user->id)
            ->where('activo', 1)
            ->where('deleted_at', null)
            ->get()->getResultArray();
        foreach ($staff as $row) {
            $ids[] = (int) $row['condominio_id'];
        }

        $persona = $db->table('personas')
            ->select('condominio_id')
            ->where('user_id', $user->id)
            ->where('deleted_at', null)
            ->get()->getResultArray();
        foreach ($persona as $row) {
            $ids[] = (int) $row['condominio_id'];
        }

        $ids = array_values(array_unique($ids));
        if ($ids === []) {
            return $this->allowedCache = [];
        }

        return $this->allowedCache = $model
            ->select('id, nombre')
            ->whereIn('id', $ids)
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();
    }

    /** @return list<int> */
    public function allowedIds(): array
    {
        return array_map(static fn (array $c): int => (int) $c['id'], $this->allowedCondominios());
    }

    /**
     * Active condominio id, defaulting to the first allowed one if unset/invalid.
     */
    public function activeId(): ?int
    {
        $allowed = $this->allowedIds();
        if ($allowed === []) {
            return null;
        }

        $current = session(self::SESSION_KEY);
        if ($current !== null && in_array((int) $current, $allowed, true)) {
            return (int) $current;
        }

        $first = $allowed[0];
        session()->set(self::SESSION_KEY, $first);

        return $first;
    }

    /**
     * Switch the active condominio. Returns false if not allowed for the user.
     */
    public function setActive(int $condominioId): bool
    {
        if (! in_array($condominioId, $this->allowedIds(), true)) {
            return false;
        }

        session()->set(self::SESSION_KEY, $condominioId);

        return true;
    }

    /** Full row of the active condominio, or null. */
    public function active(): ?array
    {
        $id = $this->activeId();

        return $id === null ? null : (new CondominioModel())->find($id);
    }
}
