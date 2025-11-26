<?php

namespace Kachnitel\DurationBundle\Tests\Unit;

use Kachnitel\DurationBundle\Form\Type\DurationType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

class DurationTypeTest extends TypeTestCase
{
    public function testSubmitValidData(): void
    {
        $formData = '2h 30m';
        $expectedData = 9000;

        $form = $this->factory->create(DurationType::class);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedData, $form->getData());
    }

    /**
     * @dataProvider submitDataProvider
     */
    public function testSubmitVariousFormats(string $input, int $expected): void
    {
        $form = $this->factory->create(DurationType::class);
        $form->submit($input);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $form->getData());
    }

    public static function submitDataProvider(): array
    {
        return [
            ['2h 30m', 9000],
            ['02:30', 9000],
            ['2.5 hours', 9000],
            ['90 minutes', 5400],
            ['1h', 3600],
            ['9000', 9000],
        ];
    }

    public function testTransformSecondsToString(): void
    {
        $form = $this->factory->create(DurationType::class, 9000);
        $view = $form->createView();

        $this->assertEquals('2h 30m', $view->vars['value']);
    }

    public function testTransformNullValue(): void
    {
        $form = $this->factory->create(DurationType::class, null);
        $view = $form->createView();

        // TextType transforms null to empty string
        $this->assertEquals('', $view->vars['value']);
    }

    public function testReverseTransformNullValue(): void
    {
        $form = $this->factory->create(DurationType::class);
        $form->submit(null);

        $this->assertEquals(0, $form->getData());
    }

    public function testParentType(): void
    {
        $type = new DurationType();
        $this->assertEquals(TextType::class, $type->getParent());
    }
}
