<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostMeta;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Specify the database connection to be used for this model
    protected $connection = 'vpAppTemplate';

    public $timestamps = true;


}
