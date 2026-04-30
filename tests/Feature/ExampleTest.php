<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_student_api_routes_are_registered(): void
    {
        $routes = Route::getRoutes();

        $this->assertSame('App\Http\Controllers\StudentController@index', $routes->match(Request::create('/api/students', 'GET'))->getActionName());
        $this->assertSame('App\Http\Controllers\StudentController@store', $routes->match(Request::create('/api/students', 'POST'))->getActionName());
        $this->assertSame('App\Http\Controllers\StudentController@show', $routes->match(Request::create('/api/students/1', 'GET'))->getActionName());
        $this->assertSame('App\Http\Controllers\StudentController@update', $routes->match(Request::create('/api/students/1', 'PUT'))->getActionName());
        $this->assertSame('App\Http\Controllers\StudentController@destroy', $routes->match(Request::create('/api/students/1', 'DELETE'))->getActionName());
    }
}
