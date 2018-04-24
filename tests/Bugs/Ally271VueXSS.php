<?php

namespace Tests\Bugs;

use App\BankAccount;
use App\Client;
use App\CreditCard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\View\Compilers\BladeCompiler;
use Tests\TestCase;

class Ally271VueXSS extends TestCase
{
    public function test_double_curly_braces_are_replaced()
    {
        $xss = '{{ alert("Hello World"); }}';

        $escaped = interpol_escape($xss);

        $this->assertEquals('❴❴ alert("Hello World"); ❵❵', $escaped);
    }

    public function test_blade_uses_interpol_escape()
    {
        $escaped = \Blade::compileString('{{ Hello World }}');

        $match = "<?php echo interpol_escape(e(";
        $this->assertEquals($match, substr($escaped, 0, strlen($match)));

    }
}