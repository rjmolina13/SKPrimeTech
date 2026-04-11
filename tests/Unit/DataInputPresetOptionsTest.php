<?php

namespace Tests\Unit;

use App\Filament\Pages\DataInput;
use Filament\Forms\Components\TextInput;
use Tests\TestCase;

class DataInputPresetOptionsTest extends TestCase
{
    public function test_short_answer_field_uses_custom_suggestion_ui(): void
    {
        $page = app(DataInput::class);

        $suggestionMethod = new \ReflectionMethod($page, 'getPresetOptions');
        $suggestionMethod->setAccessible(true);

        $suggestions = $suggestionMethod->invoke($page, [
            'preset_options' => [
                ['value' => 'SK Chairperson'],
                ['value' => 'SK Treasurer'],
                ['value' => ''],
            ],
        ]);

        $this->assertSame([
            'SK Chairperson',
            'SK Treasurer',
        ], $suggestions);

        $method = new \ReflectionMethod($page, 'createFieldComponent');
        $method->setAccessible(true);

        $field = $method->invoke($page, [
            'type' => 'text',
            'name' => 'office_name',
            'label' => 'Office Name',
            'preset_options' => [
                ['value' => 'SK Chairperson'],
                ['value' => 'SK Treasurer'],
                ['value' => ''],
            ],
        ]);

        $this->assertInstanceOf(TextInput::class, $field);
        $this->assertNull($field->getDatalistOptions());
    }
}
