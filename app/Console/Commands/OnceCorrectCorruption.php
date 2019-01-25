<?php

namespace App\Console\Commands;

use App\Billing\Payments\Methods\BankAccount;
use App\Billing\Payments\Methods\CreditCard;
use Illuminate\Console\Command;

class OnceCorrectCorruption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:corrections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ONE TIME COMMAND FOR CORRECTING DOUBLE ENCRYPTED PAYMENT METHODS';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cards = CreditCard::all();
        foreach($cards as $card) {
            $number = str_replace([' ', '-'], ['',''], $card->number);
            if (!is_numeric($number)) {
                $number = \Crypt::decrypt($number);
                if (is_numeric($number)) {
                    $this->output->writeln('Updating card number from X' . substr($card->number, -4) . ' to X' . substr($number, -4));
                    $card->update([
                        'number' => $number
                    ]);
                }
            }
        }
        unset($cards);

        $accounts = BankAccount::all();
        foreach($accounts as $account) {
            $number = str_replace([' ', '-'], ['',''], $account->account_number);
            if (!is_numeric($number)) {
                $number = \Crypt::decrypt($number);
                if (is_numeric($number)) {
                    $this->output->writeln('Updating account number from X' . substr($account->account_number, -4) . ' to X' . substr($number, -4));
                    $account->update([
                        'account_number' => $number,
                        'routing_number' => \Crypt::decrypt($account->routing_number)
                    ]);
                }
            }
        }
    }

}
