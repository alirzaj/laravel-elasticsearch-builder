<?php

namespace Alirzaj\ElasticsearchBuilder\Tests\Models;

use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends \Illuminate\Foundation\Auth\User implements AuthorizableContract, AuthenticatableContract
{
}
