<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function createUser(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->create($data);
    }

    public function updateUser($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        return $this->update($id, $data);
    }

    protected function applyFilters($query, array $filters)
    {
        if (isset($filters['role']) && $filters['role'] !== '') {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('email', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Default ordering
        $query->orderBy('created_at', 'desc');

        return $query;
    }
}
