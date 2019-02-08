<?php
namespace App\Billing\View;

use App\Contracts\ViewStrategy;
use Illuminate\Contracts\View\View;

class HtmlViewStrategy implements ViewStrategy
{

    /**
     * @param \Illuminate\Contracts\View\View $view
     * @return \Illuminate\Http\Response
     */
    public function generate(View $view)
    {
        return response($view);
    }
}