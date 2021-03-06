<?php

namespace spec\DeForm\Factory;

use DeForm\DeForm;
use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use DeForm\Node\NodeInterface;
use DeForm\Factory\ElementFactory;
use DeForm\Parser\ParserInterface;
use DeForm\Element\ElementInterface;
use DeForm\Document\DocumentInterface;
use DeForm\ValidationHelper as Validator;
use DeForm\Factory\ParserFactoryInterface;
use DeForm\Request\RequestInterface as Request;

class FormFactorySpec extends ObjectBehavior
{

    private $html = '<form></form>';

    function let(
        Request $request,
        Validator $validator,
        ElementFactory $elementFactory,
        ParserFactoryInterface $parserFactory,
        NodeInterface $formNode,
        NodeInterface $textInput,
        NodeInterface $hiddenInput,
        ElementInterface $textElement,
        DocumentInterface $document,
        ParserInterface $parser
    ) {
        $this->beConstructedWith($request, $validator, $elementFactory, $parserFactory);

        $parserFactory->createDocument($this->html)->willReturn($document);
        $parserFactory->createParser($document)->willReturn($parser);
        $parser->getFormNode()->willReturn($formNode);
        $parser->getElementsNodes()->willReturn($nodes = [
            $textInput,
        ]);

        $formNode->getAttribute('name')->willReturn($form_name = 'testform');
        $formNode->createElement('input')->shouldBeCalled()->willReturn($hiddenInput);
        $hiddenInput->setAttribute('type', 'hidden')->shouldBeCalled();
        $hiddenInput->setAttribute('value', $form_name)->shouldBeCalled();
        $hiddenInput->setAttribute('name', DeForm::DEFORM_ID)->shouldBeCalled();
        $formNode->appendChild($hiddenInput)->shouldBeCalled();

        $textInput->getElementType()->willReturn('input_text');
        $textElement->getName()->willReturn('foo');
        $elementFactory->createFromNodes($nodes)->willReturn([
            $textElement,
        ]);
    }

    function it_makes_deform_object(ElementInterface $textElement)
    {
        $form = $this->make($this->html);
        $form->shouldHaveType('DeForm\DeForm');
        $form->getElement('foo')->shouldReturn($textElement);
    }

    function it_binds_request(Request $request)
    {
        $form = $this->make($this->html);
        $request->get(DeForm::DEFORM_ID)->willReturn('testform');

        $form->isSubmitted()->shouldReturn(true);
    }

    function it_binds_validator(Request $request, Validator $validator, ElementInterface $textElement)
    {
        $request->get(DeForm::DEFORM_ID)->willReturn('testform');
        $request->get('foo')->willReturn('test');
        $textElement->getValidationRules()->willReturn('required');
        $textElement->isReadonly()->willReturn(false);
        $textElement->setValue('test')->willReturn(false);
        $textElement->getValue()->willReturn('test');

        $validator->validate(['foo' => 'required'], Argument::any())->shouldBeCalled();
        $validator->updateValidationStatus(Argument::any())->shouldBeCalled();

        $this->make($this->html)
            ->isValid();
    }
}
