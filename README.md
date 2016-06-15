# Laravel 5 Eloquent Abstract repository

using repository pattern in laravel with a great base repository


## Table of Contents

- <a href="#installation">Installation</a>
    - <a href="#composer">Composer</a>
    - <a href="#laravel">Service Provider</a>
- <a href="#methods">Methods</a>
    - <a href="#prettusrepositorycontractsrepositoryinterface">RepositoryInterface</a>

- <a href="#usage">Usage</a>
	- <a href="#create-a-model">Create a Model</a>
	- <a href="#create-a-repository">Create a Repository</a>
	- <a href="#create-service-provider">Create  service Provider</a>
	- <a href="#use-methods">Use methods</a>


## Installation

### Composer

Execute the following command to get the latest version of the package:

```terminal
composer require ra3oul/eloquent-abstract-repository -dev
```

### Laravel

In your `config/app.php` add `Prettus\Repository\Providers\RepositoryServiceProvider::class` to the end of the `providers` array:

```php
'providers' => [
    ...
    Prettus\Repository\Providers\RepositoryServiceProvider::class,
],
```

## Methods

### ra3oul\EloquentAbstractRepository\repository\RepositoryInterface

- create($columns = array('*'))
- findOneById($id )
- findOneBy($key , $value )
- findManyBy($key,$value])
- findManyByIds($ids = array())
- findAll()
- findManyByCredentials($credentials = array())
- paginateBy($key, $value, $perPage = 10)
- paginate($perPage = 10)
- paginateByCredentials(array $credentials, $perPage = 10)
- updateOneById($id, array $data = [])
- updateOneBy($key, $value, array $data = [])
- updateOneByCredentials(array $credentials, array $data = []');
- updateManyBy($key, $value, array $data = []);
-  updateManyByCredentials(array $credentials = [], array $data = []);
- updateManyByIds(array $ids, array $data = []);
- function deleteOneById($id);
-  public function allExist(array $ids);
- deleteOneBy($key, $value);
- deleteOneByCredentials(array $credentials = []);
- deleteManyBy($key, $value);
- deleteManyByCredentials(array $credentials = []);
- deleteManyByIds(array $ids);
- searchByCredentials(array $credentials = [], $perPage);
- with(array $with = []);
-  columns(array $columns = ['*']);
- limit($limit = 10);
- orderBy($orderBy, $sort = 'DESC');

## Usage

### Create a Model

Create your model normally, but it is important to define the attributes that can be filled from the input form data.

```php
namespace App;

class Article extends Eloquent { // can be any other class name
    protected $fillable = [
        'name',
        'author',
        ...
     ];

     ...
}
```
### Create a RepositoryInteface
```php
namespace App;
use Foo ;
use ra3oul\EloquentAbstractRepository\repository\RepositoryInterface;

interface ArticleRepositoryInterface extends RepositoryInterface
{

}

```

### Create a Repository

```php
namespace App;
use Foo ;
class ArticleRepository extends AbstractEloquentRepository implements ArticleRepositoryInterface


       public function __construct(Foo $model)
    {
        parent::__construct($model);
    }
}
```

### Create Service Provider
in order to bind interfaces to repository classes we should create a simple service provider to bind them


```php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected function registeredRepositories()
    {
        // 'Repository Interface' => 'Implementation'
        return [
      '\App\ArticleRepositoryInterface' =>
                '\App\ArticleRepository',
                // you should add other interfaces and their implemented classes below !
        ];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $repos = $this->registeredRepositories();

        foreach ($repos as $interface => $implemented) {
            $this->app->bind($interface, $implemented);
        }
    }
```



### Use methods

```php
namespace App\Http\Controllers;

use App\ArticleRepositoryInterface;

class ArticlesController extends BaseController {

    /**
     * @var ArticleRepository
     */
    protected $repository;

    public function __construct(FooRepositoryInterface $repository){
        $this->repository = $repository;
    }

    ....
}
```

Find all results in Repository

```php
$articles = $this->repository->findAll();
```

Find all results in Repository with pagination

```php
$aritcles = $this->repository->columns([*])->paginate($limit=10);

```

Find by result by id

```php
$articles = $this->repository->findOneById($id);
```



Showing only specific attributes of the model

```php
$article = $this->repository->columns(['id', 'state_id'])->findOneById($id);
```

Loading the Model relationships

```php
$article = $this->repository->with(['state'])->findOneById($id);
```

Find by result by field name

```php
$articles = $this->repository->findOneBy('country_id','15');
```

Find by result by  field

```php

$articles = $this->repository->findManyBy('name','rasoul');
```

Find by result by multiple values in id

```php
$posts = $this->repository->findManyByIds([1,2,3,4,5]);
```

Create new entry in Repository

```php
$post = $this->repository->create( Input::all() );
```

Update entry in Repository

```php
$post = $this->repository->updateOneById(  $id , Input::all());
```

Delete entry in Repository

```php
$this->repository->deleteOneById($id)
```


