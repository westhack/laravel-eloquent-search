# laravel-eloquent-search

一个简化 laravel model 筛选条件的库

## 介绍
如果我们想返回由多个参数筛选的用户列表:

`/users?username=er&group_id=2&roles[]=1&roles[]=4&roles[]=7&created_at[]=2019-03-05&created_at[]=2019-03-06`

`$request->all()` 的结果:
```php
[
    'username'       => 'er',
    'group_id'   => '2',
    'roles'      => ['1','4','7'],
    "created_at" => ["2019-03-05", "2019-03-06"]
]
```

要根据这些参数进行筛选，我们需要做如下工作:

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::where('group_id', $request->input('group_id'));

        if ($request->has('username'))
        {
            $query->where('username', 'LIKE', '%' . $request->input('username') . '%');
        }

        if ($request->has('created_at'))
        {
            $query->whereBetween('created_at', $request->input('created_at'));
        }

        $query->whereHas('roles', function ($q) use ($request)
        {
            return $q->whereIn('id', $request->input('roles'));
        });

        return $query->get();
    }

}
```

## 我们使用 laravel-eloquent-search 来减少工作量

##### model 引入 search trait
```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use EloquentSearch\SearchTrait;

class User extends Model
{
    use SearchTrait;
}
```

##### User Controller 

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\User;

class UserController extends BaseController
{
    public function index(Request $request)
    {
        return User::search($request->all())->get();
    }
}
```

##### 请求示例

```javascript
let search = {

    "name": {
        "column": "username",
        "operator": "like",
        "value": "er"
    },
    "group_id": {
        "column": "group_id",
        "operator": "=",
        "value": "2"
    },
    "roles": {
        "column": "roles",
        "operator": "in",
        "value": ["1","4","7"],
    },
    "created_at": {
        "column": "created_at",
        "operator": "between",
        "value": ["2019-03-05", "2019-03-06"]
    }

}

// or 

let search = {
    
    "name:like": "er",
    "group_id:=": 2,
    "roles:in": ["1","4","7"],
    "created_at:between": ["2019-03-05", "2019-03-06"]
    
}

axios.post('http://127.0.0.1:8000/users', search)
  .then(function (response) {
    console.log(response);
  })
  .catch(function (error) {
    console.log(error);
  });

```

## 安装
```bash
composer require westhack/laravel-eloquent-search
```
