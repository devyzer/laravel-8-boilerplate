<?php

namespace App\Repositories;
use Prettus\Repository\{
    Contracts\RepositoryInterface
};

/**
 * Interface RepositoryContract.
 */
interface RepositoryContract extends RepositoryInterface
{
    public function count();

    public function createMultiple(array $data);

    public function deleteById($id);

    public function deleteMultipleById(array $ids);

    public function getById($id, array $columns = ['*']);

    public function getByColumn($item, $column, array $columns = ['*']);

    public function updateById($id, array $data, array $options = []);
}
