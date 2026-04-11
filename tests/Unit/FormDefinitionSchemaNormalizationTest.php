<?php

namespace Tests\Unit;

use App\Filament\Pages\DataInput;
use App\Filament\Resources\FormDefinitionResource;
use Tests\TestCase;

class FormDefinitionSchemaNormalizationTest extends TestCase
{
    public function test_dropdown_options_are_normalized_to_unique_internal_values(): void
    {
        $schema = FormDefinitionResource::normalizeSchemaForStorage([
            [
                'type' => 'select',
                'name' => 'position',
                'options' => [
                    ['label' => 'SK Chairperson', 'value' => ''],
                    ['label' => 'SK Chairperson', 'value' => ''],
                    ['label' => 'SK Treasurer', 'value' => ''],
                ],
            ],
        ]);

        $this->assertSame([
            ['label' => 'SK Chairperson', 'value' => 'sk_chairperson'],
            ['label' => 'SK Chairperson', 'value' => 'sk_chairperson_2'],
            ['label' => 'SK Treasurer', 'value' => 'sk_treasurer'],
        ], $schema[0]['options']);
    }

    public function test_visibility_logic_accepts_dropdown_label_for_generated_internal_value(): void
    {
        $page = app(DataInput::class);

        $property = new \ReflectionProperty($page, 'activeSchema');
        $property->setAccessible(true);
        $property->setValue($page, [
            [
                'type' => 'select',
                'name' => 'position',
                'options' => [
                    ['label' => 'SK Chairperson', 'value' => 'sk_chairperson'],
                    ['label' => 'SK Treasurer', 'value' => 'sk_treasurer'],
                ],
            ],
        ]);

        $method = new \ReflectionMethod($page, 'matchesVisibleIfValue');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($page, 'position', 'SK Chairperson', 'sk_chairperson'));
        $this->assertFalse($method->invoke($page, 'position', 'SK Treasurer', 'sk_chairperson'));
    }
}
