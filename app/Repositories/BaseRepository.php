<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Prettus\Repository\Eloquent\BaseRepository as PrettusBaseRepository;
use Prettus\Repository\Events\RepositoryEntityDeleted;
use Illuminate\Container\Container as Application;
use \Illuminate\Http\Request;

/**
 * Class BaseRepository.
 */
abstract class BaseRepository extends PrettusBaseRepository implements RepositoryContract
{

    protected $defaultOrderBy;
    protected $defaultSortBy;

    public function __construct(Application $app, Request $request)
    {
        parent::__construct($app);

        $orderBy = config('repository.criteria.params.orderBy', 'orderBy');
        $sortedBy = config('repository.criteria.params.sortedBy', 'sortedBy');

        if (!$request->has($orderBy) && $this->defaultOrderBy) {
            $request->request->add([$orderBy => $this->defaultOrderBy]);
        }

        if (!$request->has($sortedBy) && $request->has($orderBy) && $this->defaultSortBy) {
            $request->request->add([$sortedBy => $this->defaultSortBy]);
        }

    }

    /**
     * Create one or more new model records in the database.
     *
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function createMultiple(array $data)
    {
        $models = new Collection();

        foreach ($data as $d) {
            $models->push($this->create($d));
        }

        return $models;
    }

    /**
     * Delete one or more model records from the database.
     *
     * @param null $id optional
     * @return mixed
     */
    public function delete($model = null): bool
    {
        $id = null;

        if ($model instanceof Model)
            $id = $model->id;

        if ($id != null)
            return $this->deleteById($id);

        $this->applyCriteria();
        $this->applyScope();

        $temporarySkipPresenter = $this->skipPresenter;
        $this->skipPresenter(true);

        $model = $this->model;
        $originalModel = clone $model;

        $this->skipPresenter($temporarySkipPresenter);
        $this->resetModel();
        $this->resetScope();

        if ($model instanceof Builder) {
            $deleted = $model->delete();
        } else {
            $deleted = $model->delete();
        }

        event(new RepositoryEntityDeleted($this, $originalModel));

        return $deleted;
    }

    /**
     * Delete the specified model record from the database.
     *
     * @param $id
     *
     * @return bool|null
     * @throws \Exception
     */
    public function deleteById($id): bool
    {
        return parent::delete($id);
    }


    /**
     * Delete multiple records.
     *
     * @param array $ids
     *
     * @return int
     */
    public function deleteMultipleById(array $ids): int
    {
        return $this->model->destroy($ids);
    }


    /**
     * Get the specified model record from the database.
     *
     * @param       $id
     * @param array $columns
     *
     * @return Collection|Model
     */
    public function getById($id, array $columns = ['*'])
    {
        return parent::find($id, $columns);
    }

    /**
     * @param       $item
     * @param       $column
     * @param array $columns
     *
     * @return Model|null|static
     */
    public function getByColumn($item, $column, array $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->where($column, '=', $item)->get($columns);
        $this->resetModel();

        return $this->parserResult($model);
    }

    /**
     * Update the specified model record in the database.
     *
     * @param       $id
     * @param array $data
     * @param array $options
     *
     * @return Collection|Model
     */
    public function updateById($id, array $data, array $options = [])
    {
        return parent::update($data, $id);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        if (is_numeric($id))
            return parent::find($id, $columns);
        else
            return $this->findByField('slug', $id, $columns)->first();
    }


    public function count($columns = '*')
    {
        $this->applyCriteria();
        $this->applyScope();

        if ($this->model instanceof Builder) {
            $results = $this->model->count($columns);
        } else {
            $results = $this->model->count($columns);
        }

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }


    public function sum($column)
    {
        $this->applyCriteria();
        $this->applyScope();

        if ($this->model instanceof Builder) {
            $results = $this->model->sum($column);
        } else {
            $results = $this->model->sum($column);
        }

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }


    public function countGroupBy($rawSelectColumns, $groupColumns)
    {

        $this->applyCriteria();
        $this->applyScope();


        $select = DB::raw(implode(',', $rawSelectColumns) . ' , count(\'*\') as count');
        if ($this->model instanceof Builder) {
            $results = $this->model->select($select)->groupBy($groupColumns)->orderBy('count', 'des')->get();
        } else {
            $results = $this->model->select($select)->groupBy($groupColumns)->orderBy('count', 'des')->get();
        }

        $this->resetModel();
        $this->resetScope();

        return $this->parserResult($results);
    }


    public function update($model, $attributes)
    {

        if ($model instanceof Model)
            parent::update($attributes, $model->id);
        else
            parent::update($attributes, $model);
    }
}
