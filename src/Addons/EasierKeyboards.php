<?php declare(strict_types=1);

namespace Sakura\Addons;


class EasierKeyboards
{
    const INFO = [
        'version' => '1.0',
        'description' => 'An class to create Telegram inline keyboards more easly.',
        'author' => 't.me/JustLel',
    ];
    private $rows;
    private $rows_number;
    private $current_row = 0;

    public function __construct(int $rows_count) {
        $this->rows['inline_keyboard'] = [];
        $this->rows_number = $rows_count;
    }

    public function divideRow(array $text, array $keyboards) {
        $button_text_row = 0;
        foreach ($text as $key_text) {
            $this->rows['inline_keyboard'][$this->current_row][$button_text_row]['text'] = $key_text;
            $button_text_row++;
        }
        $button_data_row = 0;
        foreach($keyboards as $key_value) {
            if(stripos($key_value, 'www.')===0) {
                $key_value = 'https://'.$key_value;
            }
            if(filter_var($key_value, FILTER_VALIDATE_URL)) {
                $this->rows['inline_keyboard'][$this->current_row][$button_data_row]['url'] = $key_value;
            } else {
                $this->rows['inline_keyboard'][$this->current_row][$button_data_row]['callback_data'] = $key_value;
            }
            $button_data_row++;
        }
        return $this;
    }

    public function carriageReturn() {
        $this->current_row++;
        return $this;
    }

    public function done() {
        if(count($this->rows['inline_keyboard'])!==$this->rows_number) {
            die('You have not filled up all the rows.');
        }
        $built = json_encode($this->rows);
        return $built;
    }
}