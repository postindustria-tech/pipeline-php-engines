<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2019 51 Degrees Mobile Experts Limited, 5 Charlotte Close,
 * Caversham, Reading, Berkshire, United Kingdom RG4 7BY.
 *
 * This Original Work is licensed under the European Union Public Licence (EUPL)
 * v.1.2 and is subject to its terms as set out below.
 *
 * If a copy of the EUPL was not distributed with this file, You can obtain
 * one at https://opensource.org/licenses/EUPL-1.2.
 *
 * The 'Compatible Licences' set out in the Appendix to the EUPL (as may be
 * amended by the European Commission) shall be deemed incompatible for
 * the purposes of the Work and the provisions of the compatibility
 * clause in Article 5 of the EUPL shall not apply.
 *
 * If using the Work as, or as part of, a network application, by
 * including the attribution notice(s) required under Article 5 of the EUPL
 * in the end user terms of the application under an appropriate heading,
 * such notice(s) shall fulfill the requirements of that article.
 * ********************************************************************* */

namespace fiftyone\pipeline\engines\tests;

require(__DIR__ . "/../vendor/autoload.php");

use fiftyone\pipeline\engines;

use PHPUnit\Framework\TestCase;

class MissingPropertyTests extends TestCase {
    
    public function testUpgradeRequired()
    {
        $service = new engines\MissingPropertyService();
        
        $engine = $this->createStub(engines\Engine::class);

        $properties = array(
            "testProperty" => array(
                "name" => "testProperty",
                "type" => "string",
                "datatierswherepresent" => [ "premium" ]
            )
        );
        
        $engine->method('getProperties')
             ->willReturn($properties);
        $engine->method('getDataSourceTier')
            ->willReturn("lite");
        $engine->dataKey = "testElement";

        // Assert
        try {
            $service->check("testProperty", $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(
                sprintf(engines\MissingPropertyMessages::PREFIX,
                        "testProperty",
                        "testElement") .
                sprintf(engines\MissingPropertyMessages::DATA_UPGRADE_REQUIRED,
                        "premium",
                        get_class($engine)),
                $e->getMessage());
        }
    }
    
    public function testExcluded()
    {
        $service = new engines\MissingPropertyService();
        
        $engine = $this->createStub(engines\Engine::class);

        $properties = array(
            "testProperty" => array(
                "name" => "testProperty",
                "type" => "string",
                "datatierswherepresent" => [ "lite" ],
                "available" => false
            )
        );
        
        $engine->method('getProperties')
             ->willReturn($properties);
        $engine->method('getDataSourceTier')
            ->willReturn("lite");
        $engine->dataKey = "testElement";

        // Assert
        try {
            $service->check("testProperty", $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(
                sprintf(engines\MissingPropertyMessages::PREFIX,
                        "testProperty",
                        "testElement") .
                engines\MissingPropertyMessages::PROPERTY_EXCLUDED,
                $e->getMessage());
        }
    }
    
    public function testNotInEngine()
    {
        $service = new engines\MissingPropertyService();
        
        $engine = $this->createStub(engines\Engine::class);

        $properties = array(
            "testProperty" => array(
                "name" => "testProperty",
                "type" => "string",
                "datatierswherepresent" => [ "premium" ],
                "available" => false
            )
        );
        
        $engine->method('getProperties')
             ->willReturn($properties);
        $engine->method('getDataSourceTier')
            ->willReturn("lite");
        $engine->dataKey = "testElement";

        // Assert
        try {
            $service->check("otherProperty", $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertEquals(
                sprintf(engines\MissingPropertyMessages::PREFIX,
                        "otherProperty",
                        "testElement") .
                engines\MissingPropertyMessages::UNKNOWN,
                $e->getMessage());
        }
    }
}
