<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2022
 */


namespace Aimeos\Client\Html\Account\Review;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private $object;
	private $context;
	private $view;


	protected function setUp() : void
	{
		$this->view = \TestHelper::view();
		$this->context = \TestHelper::context();

		$this->object = new \Aimeos\Client\Html\Account\Review\Standard( $this->context );
		$this->object->setView( $this->view );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context, $this->view );
	}


	public function testHeader()
	{
		$output = $this->object->header();

		$this->assertStringContainsString( '<link rel="stylesheet"', $output );
		$this->assertStringContainsString( '<script defer', $output );
	}


	public function testBody()
	{
		$manager = \Aimeos\MShop::create( $this->context, 'customer' );
		$this->context->setUserId( $manager->find( 'test@example.com' )->getId() );

		$this->view = $this->object->data( $this->view );
		$this->view->reviewProductItems = map( \Aimeos\MShop::create( $this->context, 'product' )->find( 'CNE' ) );

		$output = $this->object->body();

		$this->assertStringContainsString( '<section class="aimeos account-review"', $output );
		$this->assertStringContainsString( 'ABCD/16 discs', $output );
	}


	public function getInit()
	{
		$param = ['review-todo' => [['review.rating' => 5]]];
		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $this->view, $param );
		$this->view->addHelper( 'param', $helper );
		$this->object->setView( $this->view );

		$stub = $this->getMockBuilder( \Aimeos\Controller\Frontend\Review\Standard::class )
			->setConstructorArgs( [$this->context] )
			->setMethods( ['save'] )
			->getMock();

		$stub->expects( $this->once() )->method( 'save' );

		\Aimeos\Controller\Frontend\Review\Factory::injectController( '\Aimeos\Controller\Frontend\Review\Standard', $stub );
		$this->object->init();
		\Aimeos\Controller\Frontend\Review\Factory::injectController( '\Aimeos\Controller\Frontend\Review\Standard', null );

		$this->assertCount( 1, $this->view->get( 'reviewInfoList' ) );
	}
}