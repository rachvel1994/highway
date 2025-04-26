<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

class PriceInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->type('text')
            ->default(0)
            ->postfix('₾')
            ->reactive()
            ->required()
            ->debounce(300)
            ->numeric()
            ->extraAttributes([
                'x-init' => '
                    $el.addEventListener("input", (e) => {
                        e.target.value = e.target.value.replace(",", ".");
                    });
                ',
            ]);
    }
}
