<?php
/* *********************************************************************
 * This Original Work is copyright of 51 Degrees Mobile Experts Limited.
 * Copyright 2023 51 Degrees Mobile Experts Limited, Davidson House,
 * Forbury Square, Reading, Berkshire, United Kingdom RG1 3EU.
 *
 * This Original Work is licensed under the European Union Public Licence
 * (EUPL) v.1.2 and is subject to its terms as set out below.
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

use fiftyone\pipeline\engines\CloudEngineBase;
use fiftyone\pipeline\engines\Engine;
use fiftyone\pipeline\engines\MissingPropertyMessages;
use fiftyone\pipeline\engines\MissingPropertyService;
use PHPUnit\Framework\TestCase;

class MissingPropertyTests extends TestCase
{
    /**
     * Check that when an engine contains the property, but in another data
     * tier, an "upgrade required" exception message is returned.
     */
    public function testUpgradeRequired()
    {
        $properties = [
            'testProperty' => [
                'name' => 'testProperty',
                'type' => 'string',
                'datatierswherepresent' => ['premium']
            ]
        ];

        $engine = $this->createMock(Engine::class);
        $engine->method('getProperties')->willReturn($properties);
        $engine->method('getDataSourceTier')->willReturn('lite');
        $engine->dataKey = 'testElement';

        $errorMessage = sprintf(MissingPropertyMessages::PREFIX, 'testProperty', 'testElement');
        $errorMessage .= sprintf(MissingPropertyMessages::DATA_UPGRADE_REQUIRED, 'premium', get_class($engine));

        $service = new MissingPropertyService();

        try {
            $service->check('testProperty', $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame($errorMessage, $e->getMessage());
        }
    }

    /**
     * Check that when an engine contains the property, but it is not marked as
     * available, an "excluded" exception message is returned.
     */
    public function testExcluded()
    {
        $properties = [
            'testProperty' => [
                'name' => 'testProperty',
                'type' => 'string',
                'datatierswherepresent' => ['lite'],
                'available' => false
            ]
        ];

        $engine = $this->createMock(Engine::class);

        $engine->method('getProperties')->willReturn($properties);
        $engine->method('getDataSourceTier')->willReturn('lite');
        $engine->dataKey = 'testElement';

        $errorMessage = sprintf(MissingPropertyMessages::PREFIX, 'testProperty', 'testElement');
        $errorMessage .= MissingPropertyMessages::PROPERTY_EXCLUDED;

        $service = new MissingPropertyService();

        try {
            $service->check('testProperty', $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame($errorMessage, $e->getMessage());
        }
    }

    /**
     * Check that when an engine does not contain the property, an "unknown"
     * exception message is returned.
     */
    public function testNotInEngine()
    {
        $properties = [
            'testProperty' => [
                'name' => 'testProperty',
                'type' => 'string',
                'datatierswherepresent' => ['premium'],
                'available' => false
            ]
        ];

        $engine = $this->createMock(Engine::class);
        $engine->method('getProperties')->willReturn($properties);
        $engine->method('getDataSourceTier')->willReturn('lite');
        $engine->dataKey = 'testElement';

        $errorMessage = sprintf(MissingPropertyMessages::PREFIX, 'otherProperty', 'testElement');
        $errorMessage .= MissingPropertyMessages::UNKNOWN;

        $service = new MissingPropertyService();

        try {
            $service->check('otherProperty', $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame($errorMessage, $e->getMessage());
        }
    }

    /**
     * Check that when a cloud engine does not contain the product, a "product
     * not in resource" exception message is returned.
     */
    public function testProductNotInResource()
    {
        $properties = [];

        $engine = $this->createMock(CloudEngineBase::class);
        $engine->method('getProperties')->willReturn($properties);
        $engine->method('getDataSourceTier')->willReturn('lite');
        $engine->dataKey = 'testElement';

        $errorMessage = sprintf(MissingPropertyMessages::PREFIX, 'testProperty', 'testElement');
        $errorMessage .= sprintf(MissingPropertyMessages::PRODUCT_NOT_IN_CLOUD_RESOURCE, get_class($engine));

        $service = new MissingPropertyService();

        try {
            $service->check('testProperty', $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame($errorMessage, $e->getMessage());
        }
    }

    /**
     * Check that when a cloud engine does not contain the property, a "property
     * not in resource" exception message is returned.
     */
    public function testPropertyNotInResource()
    {
        $properties = [
            'testProperty' => [
                'name' => 'testProperty',
                'type' => 'string',
                'datatierswherepresent' => ['premium'],
                'available' => false
            ]
        ];

        $engine = $this->createMock(CloudEngineBase::class);
        $engine->method('getProperties')->willReturn($properties);
        $engine->method('getDataSourceTier')->willReturn('lite');
        $engine->dataKey = 'testElement';

        $errorMessage = sprintf(MissingPropertyMessages::PREFIX, 'otherProperty', 'testElement');
        $errorMessage .= sprintf(MissingPropertyMessages::PROPERTY_NOT_IN_CLOUD_RESOURCE, 'testElement', 'testProperty');

        $service = new MissingPropertyService();

        try {
            $service->check('otherProperty', $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame($errorMessage, $e->getMessage());
        }
    }

    /**
     * Check that when none of the above are true, an "unknown" exception
     * message is returned.
     */
    public function testUnknown()
    {
        $properties = [
            'testProperty' => [
                'name' => 'testProperty',
                'type' => 'string',
                'datatierswherepresent' => ['premium'],
                'available' => true
            ]
        ];

        $engine = $this->createMock(Engine::class);
        $engine->method('getProperties')->willReturn($properties);
        $engine->method('getDataSourceTier')->willReturn('premium');
        $engine->dataKey = 'testElement';

        $errorMessage = sprintf(MissingPropertyMessages::PREFIX, 'testProperty', 'testElement');
        $errorMessage .= MissingPropertyMessages::UNKNOWN;

        $service = new MissingPropertyService();

        try {
            $service->check('testProperty', $engine);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertSame($errorMessage, $e->getMessage());
        }
    }
}
