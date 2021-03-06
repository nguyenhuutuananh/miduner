<?php

namespace App\Http\Controllers;

use Request;
use App\Http\Requests\TestRequestFile;
use App\Repositories\User\UserInterface;

class HomeController extends Controller
{
    public $userRepository;

    public function __construct(
        UserInterface $userRepository
    ) {
        $this->userRepository = $userRepository;
    }

    public function home(Request $request)
    {
        dd($this->userRepository->find(3099));
        return view('pages/home');
    }

    public function testPostFile(TestRequestFile $request)
    {

    }

    public function about()
    {
        return view('pages/about');
    }

    public function post()
    {
        return view('pages/post');
    }

    public function contact()
    {
        return view('pages/contact');
    }
}
