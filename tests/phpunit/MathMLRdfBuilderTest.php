<?php

use DataValues\StringValue;
use Wikibase\DataModel\Entity\PropertyId;
use Wikibase\DataModel\Snak\PropertyValueSnak;
use Wikimedia\Purtle\NTriplesRdfWriter;

/**
 * Test the MathML RDF formatter
 *
 * @group Math
 * @covers \MathMLRdfBuilder
 * @author Moritz Schubotz (physikerwelt)
 */
class MathMLRdfBuilderTest extends MediaWikiTestCase {
	use MockHttpTrait;

	private const ACME_PREFIX_URL = 'http://acme/';
	private const ACME_REF = 'testing';

	/**
	 * @param string $test
	 * @return string
	 */
	private function makeCase( $test ) {
		$builder = new MathMLRdfBuilder();
		$writer = new NTriplesRdfWriter();
		$writer->prefix( 'www', "http://www/" );
		$writer->prefix( 'acme', self::ACME_PREFIX_URL );

		$writer->start();
		$writer->about( 'www', 'Q1' );

		$snak = new PropertyValueSnak( new PropertyId( 'P1' ), new StringValue( $test ) );
		$builder->addValue( $writer, 'acme', self::ACME_REF, 'DUMMY', '', $snak );

		return trim( $writer->drain() );
	}

	public function testValidInput() {
		$this->installMockHttp( [
			$this->makeFakeHttpRequest( file_get_contents( __DIR__ .
				'/InputCheck/data/sinx.json' ) ),
			$this->makeFakeHttpRequest( file_get_contents( __DIR__ .
				'/data/sinx.json' ) ),
		] );
		$triples = $this->makeCase( '\sin x' );
		$this->assertStringContainsString(
			self::ACME_PREFIX_URL . self::ACME_REF . '> "<math',
			$triples
		);
		$this->assertStringContainsString( '>x</mi>', $triples );
		// be conservative in the test mi should become mo in the future
		$this->assertStringContainsString( '>sin</m', $triples );
		$this->assertStringContainsString( '\sin x', $triples );
		$this->assertStringContainsString( '^^<http://www.w3.org/1998/Math/MathML> .', $triples );
	}

	public function testInvalidInput() {
		$this->installMockHttp( [
			$this->makeFakeHttpRequest( file_get_contents( __DIR__ .
				'/InputCheck/data/invalidF.json' ), 400 )
		] );
		$triples = $this->makeCase( '\notExists' );
		$this->assertStringContainsString( '<math', $triples );
		$this->assertStringContainsString( 'unknown function', $triples );
		$this->assertStringContainsString( 'notExists', $triples );
		$this->assertStringContainsString( '^^<http://www.w3.org/1998/Math/MathML> .', $triples );
	}
}
