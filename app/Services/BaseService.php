<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class BaseService
{
    protected $model;

    public function getAll(array $filters = [])
    {
        try {
            $query = $this->model->query();
            $query = $this->applyFilters($query, $filters);
            return $query->get();
        } catch (Exception $e) {
            Log::error('Error in getAll: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPaginated(array $filters = [], int $perPage = 10)
    {
        try {
            $query = $this->model->query();
            $query = $this->applyFilters($query, $filters);
            return $query->paginate($perPage);
        } catch (Exception $e) {
            Log::error('Error in getPaginated: ' . $e->getMessage());
            throw $e;
        }
    }

    public function findById($id)
    {
        try {
            return $this->model->findOrFail($id);
        } catch (Exception $e) {
            Log::error('Error in findById: ' . $e->getMessage());
            throw $e;
        }
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $record = $this->model->create($data);
            DB::commit();
            return $record;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in create: ' . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $record = $this->findById($id);
            $record->update($data);
            DB::commit();
            return $record->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in update: ' . $e->getMessage());
            throw $e;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $record = $this->findById($id);
            $record->delete();
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in delete: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function applyFilters($query, array $filters)
    {
        return $query;
    }
}
