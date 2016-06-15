<?php

/**
 * Created by PhpStorm.
 * User: ra3oul
 * Date: 6/15/16
 * Time: 12:16 PM
 */
namespace ra3oul\EloquentAbstractRepository\repository\eloquent ;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use ra3oul\EloquentAbstractRepository\InvalidDataProvidedException;
use ra3oul\EloquentAbstractRepository\repository\RepositoryInterface;
use ra3oul\EloquentAbstractRepository\RepositoryException;

abstract class AbstractEloquentRepository implements RepositoryInterface
{

    protected $model;
    protected $query;

    /**
     * @var array $data
     * query parameters (sort, filters, pagination)
     */
    protected $data;
    protected $with = [];
    protected $columns = ['*'];
    protected $orderBy;
    protected $sortMethod = 'DESC';
    protected $limit = 10;
    protected $offset = 0;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->orderBy = $model->getKeyName();
    }

    protected function buildCredentialsQuery(array $credentials)
    {
        $results = $this->makeQuery();

        if (!empty($credentials)){

            foreach ($credentials as $key => $_value) {
                $value = $_value;
                $operator = '=';

                if (is_array($_value)) {
                    $value = $_value[0];
                    $operator = isset($_value[1]) ? $_value[1] : $operator;

                    if (is_array($_value[0])) {
                        foreach ($_value as $__value) {
                            $value = $__value[0];
                            $operator = isset($__value[1]) ? $__value[1] : $operator;
                            $hasAndOperator = isset($__value[2]) && (strtolower($__value[2]) != 'and') ? false : true;

                            if ($hasAndOperator) {
                                $results = $results->where($key, $operator, $value);
                            } else {
                                $results = $results->OrWhere($key, $operator, $value);
                            }
                        }
                    } else {
                        $results = $results->where($key, $operator, $value);
                    }
                } else {
                    $results = $results->where($key, $operator, $value);


                }

            }
        }

        return $results;
    }

    /**
     * @param array $with
     * @return $this
     * @throws RepositoryException
     */
    public function with(array $with = [])
    {
        if (is_array($with) === false) {
            throw new RepositoryException('');
        }

        $this->with = $with;

        return $this;
    }

    /**
     * @param array $columns
     * @return $this
     * @throws RepositoryException
     */
    public function columns(array $columns = ['*'])
    {
        if (is_array($columns) === false) {
            throw new RepositoryException('');
        }

        $this->columns = $columns;

        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     * @throws RepositoryException
     */
    public function limit($limit = 10)
    {
        if (!is_numeric($limit) || $limit < 1) {
            throw new RepositoryException('Limit Must be greater than 1');
        }

        $this->limit = $limit;

        return $this;

    }

    /**
     * @param $id
     * @param $field
     */
    public function inc($id, $field)
    {
        $this->model->findOrFail($id)->increment($field);
    }

    /**
     * @param $id
     * @param $field
     */
    public function dec($id, $field)
    {
        $this->model->find($id)->decrement($field);
    }

    /**
     * @param int $offset
     * @return $this
     * @throws RepositoryException
     */
    public function offset($offset = 0)
    {
        if (!is_numeric($offset) || $offset < 0) {
            throw new RepositoryException('Offset must be grater than or equal to ZERO');
        }

        $this->offset = $offset;

        return $this;
    }


    /**
     * @param $orderBy
     * @param string $sort
     * @return $this
     * @throws RepositoryException
     */
    public function orderBy($orderBy = null, $sort = 'DESC')
    {
        if ($orderBy === null)
            return $this;

        $this->orderBy = $orderBy;

        if (!in_array(strtoupper($sort), ['DESC', 'ASC'])) {
            throw new RepositoryException('');
        }

        $this->sortMethod = $sort;

        return $this;
    }

    protected function makeQuery()
    {
        return $this->model;
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findOneById($id)
    {
        return $this->makeQuery()
            ->with($this->with)
            ->findOrFail($id, $this->columns);
    }

    /**
     * @param array $ids
     * @return mixed
     */
    public function findManyByIds(array $ids)
    {
        return $this->makeQuery()
            ->with($this->with)
            ->whereIn($this->model->getKeyName(), $ids)
            ->take($this->limit)
            ->skip($this->offset)
            ->get($this->columns);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function findOneBy($key, $value)
    {
        return $this->makeQuery()
            ->with($this->with)
            ->where($key, '=', $value)
            ->firstOrFail($this->columns);
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    public function findOneByNotRaiseException($key, $value)
    {
        return $this->makeQuery()
            ->with($this->with)
            ->where($key, '=', $value)
            ->first($this->columns);
    }


    /**
     * @param $key
     * @param $value
     * @return Collection
     */
    public function findManyBy($key, $value)
    {
        return $this->makeQuery()
            ->with($this->with)
            ->where($key, '=', $value)
            ->orderBy($this->orderBy, $this->sortMethod)
            ->take($this->limit)
            ->skip($this->offset)
            ->get($this->columns);
    }

    /**
     * @return Collection
     */
    public function findAll()
    {
        return $this->makeQuery()
            ->with($this->with)
            ->orderBy($this->orderBy, $this->sortMethod)
            ->get($this->columns);
    }

    /**
     * @return Collection
     */
    public function findMany()
    {
        return $this->makeQuery()
            ->with($this->with)
            ->orderBy($this->orderBy, $this->sortMethod)
            ->take($this->limit)
            ->skip($this->offset)
            ->get($this->columns);
    }

    /**
     * @param array $credentials
     * @return Collection
     */
    public function findManyByCredentials(array $credentials = [])
    {
        return $this->buildCredentialsQuery($credentials)
            ->with($this->with)
            ->orderBy($this->orderBy, $this->sortMethod)
            ->take($this->limit)
            ->skip($this->offset)
            ->get($this->columns);
    }

    /**
     * @param array $credentials
     * @return Collection | ModelNotFoundException
     */
    public function findOneByCredentials(array $credentials = [])
    {
        return $this->buildCredentialsQuery($credentials)
            ->with($this->with)
            ->firstOrFail($this->columns);
    }

    /**
     * @param $key
     * @param $value
     * @param int $perPage
     * @return mixed
     */
    public function paginateBy($key, $value, $perPage = 10)
    {
        return $this->makeQuery()
            ->with($this->with)
            ->where($key, '=', $value)
            ->orderBy($this->orderBy, $this->sortMethod)
            ->paginate($perPage, $this->columns);

    }

    /**
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage = 10)
    {

        return $this->makeQuery()
            ->with($this->with)
            ->orderBy($this->orderBy, $this->sortMethod)
            ->paginate($perPage, $this->columns);
    }

    /**
     * @param $query
     * @param int $perPage
     * @return mixed
     */
    public function paginateQuery($query, $perPage = 10)
    {
        return $query->orderBy($this->orderBy, $this->sortMethod)
            ->paginate($perPage, $this->columns);
    }

    /**
     * @param $credentials
     * @param int $perPage
     * @return Paginator
     */
    public function paginateByCredentials($credentials, $perPage = 10)
    {
        return $this->buildCredentialsQuery($credentials)
            ->with($this->with)
            ->orderBy($this->orderBy, $this->sortMethod)
            ->paginate($perPage, $this->columns);
    }

    /**
     * @param $id
     * @param array $data
     * @return bool
     * @throws InvalidDataProvidedException
     */
    public function updateOneById($id, array $data = [])
    {
        if(!is_array($data) || empty($data))
            throw new InvalidDataProvidedException;

        return $this->makeQuery()
            ->findOrFail($id)
            ->update($data);
    }

    /**
     * @param $key
     * @param $value
     * @param array $data
     * @return bool
     * @throws InvalidDataProvidedException
     */
    public function updateOneBy($key, $value, array $data = [])
    {
        if(is_array($data) || empty($data))
            throw new InvalidDataProvidedException;

        return $this->makeQuery()
            ->where($key, '=', $value)
            ->firstOrFail()
            ->update($data);
    }

    /**
     * @param array $credentials
     * @param array $data
     * @return bool
     * @throws InvalidDataProvidedException
     */
    public function updateOneByCredentials(array $credentials, array $data = [])
    {
        if(is_array($data) || empty($data))
            throw new InvalidDataProvidedException;

        return $this->buildCredentialsQuery($credentials)
            ->firstOrFail()
            ->update($data);
    }

    /**
     * @param $key
     * @param $value
     * @param array $data
     * @return bool
     * @throws InvalidDataProvidedException
     */
    public function updateManyBy($key, $value, array $data = [])
    {
        if(is_array($data) || empty($data))
            throw new InvalidDataProvidedException;

        return $this->makeQuery()
            ->where($key, $value)
            ->take($this->limit)
            ->skip($this->offset)
            ->update($data);
    }


    /**
     * @param array $ids
     * @param array $data
     * @return bool
     * @throws InvalidDataProvidedException
     */
    public function updateManyByIds(array $ids, array $data = [])
    {
        if(!is_array($data) || empty($data))
            throw new InvalidDataProvidedException;

        return $this->makeQuery()
            ->whereIn('id', $ids)
            ->update($data);
    }

    /**
     * @param array $ids
     * @return bool
     */
    public function allExist(array $ids)
    {
        return (count($ids) == $this->makeQuery()->whereIn('id',$ids)->count());
    }


    /**
     * @param array $credentials
     * @param array $data
     * @return boolean
     */
    public function updateManyByCredentials(array $credentials = [], array $data = [])
    {
        return $this->buildCredentialsQuery($credentials)->update($data);
    }

    /**
     * @param array $credentials
     * @param array $data
     * @return mixed
     */
    public function updateAllByCredentials(array $credentials = [], array $data = [])
    {
        return $this->buildCredentialsQuery($credentials)
            ->update($data);
    }

    /**
     * @param $id
     * @return boolean
     */
    public function deleteOneById($id)
    {
        return $this->makeQuery()
            ->findOrFail($id)
            ->delete();
    }

    /**
     * @param $key
     * @param $value
     * @return boolean
     */
    public function deleteOneBy($key, $value)
    {
        return $this->makeQuery()
            ->where($key, '=', $value)
            ->firstOrFail()
            ->delete();
    }

    /**
     * @param array $credentials
     * @return boolean
     */
    public function deleteOneByCredentials(array $credentials = [])
    {
        return $this->buildCredentialsQuery($credentials)
            ->firstOrFail()
            ->delete();
    }

    /**
     * @param $key
     * @param $value
     * @return boolean
     */
    public function deleteManyBy($key, $value)
    {
        return $this->makeQuery()
            ->where($key, $value)
            ->take($this->limit)
            ->skip($this->offset)
            ->delete();
    }

    /**
     * @param array $credentials
     * @return boolean
     */
    public function deleteManyByCredentials(array $credentials = [])
    {
        return $this->buildCredentialsQuery($credentials)
            ->take($this->limit)
            ->skip($this->offset)
            ->delete();
    }

    /**
     * @return mixed
     */
    public function deleteMany()
    {
        return $this->makeQuery()
            ->take($this->limit)
            ->delete();
    }


    /**
     * @param array $ids
     * @return mixed
     */
    public function deleteManyByIds(array $ids)
    {
        return $this->makeQuery()
            ->whereIn('id', $ids)
            ->delete();
    }

    /**
     * @return bool|null
     * @throws \Exception
     */
    public function deleteAll()
    {
        return $this->makeQuery()
            ->delete();
    }

    /**
     * @param $credentials
     * @return bool|null
     * @throws \Exception
     */
    public function deleteAllByCredentials($credentials)
    {
        return $this->buildCredentialsQuery($credentials)
            ->delete();
    }


    /**
     * @param array $credentials
     * @param int $perPage
     *
     *
     * samples :
     * categories:in_relation:we|wed|wef|edf
     * mobile:in:123|23|23|234
     * name:=:majid
     * age:<:20
     * age:>:10
     * family:like:%asghar%
     *
     * @return mixed
     * @throws RepositoryException
     */
    public function searchByCredentials(array $credentials = [], $perPage = 10)
    {
        $items = [];

        foreach ($credentials as $key => $value) {

            if (!is_array($value)) {
                throw new RepositoryException();
            }
            $items[] = ['key' => $key, 'operator' => $value[1], 'value' => $value[0]];

        }
        $result = $this->model;
        foreach ($items as $item) {

            if (count($relation = explode('.', $item['key'])) === 2) {
                $result = $result->whereHas($relation[0], function ($query) use ($relation, $item) {
                    $query->where($relation[1], $item['operator'], $item['value']);
                });
            }elseif ($item['operator'] == 'in'){
                $result = $result->whereIn($item['key'],explode('|', $item['value']) );
            }elseif ($item['operator'] == 'in_relation'){
                $result = $result->whereHas($item['key'], function($q) use ($item) {
                    $q->whereIn(str_singular($item['key']).'_id', explode('|', $item['value']));
                });
            }

            else {
                $result = $result->Where($item['key'], $item['operator'], $item['value']);
            }
        }

        return $result->with($this->with)->orderBy($this->orderBy, $this->sortMethod)->paginate($perPage, $this->columns);

    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function add(array $data)
    {
        $item = $this->model;
        foreach($data as $k=>$v)
            $item->{$k} = $v;

        if(array_key_exists('slug', $data))
            $item->slug = slugify(array_key_exists('name',$data)? $data['name']:$data['title']);

        $item->save();

        return $item;
    }




}
