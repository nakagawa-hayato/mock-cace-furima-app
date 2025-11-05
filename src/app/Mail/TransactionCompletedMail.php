<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Item;
use App\Models\User;

class TransactionCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var \App\Models\Item
     */
    public $item;

    /**
     * @var \App\Models\User
     */
    public $rater;

    /**
     * Create a new message instance.
     *
     * Note:
     * この Mailable は評価（score）やコメント（comment）を含めません。
     * メールは「取引が完了した」旨のみ通知します。
     *
     * @param \App\Models\Item $item
     * @param \App\Models\User $rater  取引を完了したユーザー（通知対象に誰が取引を完了したかを示す）
     */
    public function __construct(Item $item, User $rater)
    {
        $this->item = $item;
        $this->rater = $rater;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('あなたの出品した商品の取引が完了しました')
                    ->view('transaction_completed') // resources/views/transaction_completed.blade.php
                    ->with([
                        'item'  => $this->item,
                        'rater' => $this->rater,
                    ]);
    }
}
