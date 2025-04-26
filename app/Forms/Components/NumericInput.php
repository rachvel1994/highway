<?php

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

class NumericInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->type('text')
            ->default(0)
            ->reactive()
            ->required()
            ->minValue(0)
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
