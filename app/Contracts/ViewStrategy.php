<?php
namespace App\Contracts;

use Illuminate\Contracts\View\View;

interface ViewStrategy
{
    /**
     * @param \Illuminate\Contracts\View\View $view
     * @return \Illuminate\Http\Response
     */
    public function generate(View $view);
}