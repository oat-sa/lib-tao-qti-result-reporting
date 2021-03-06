<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2016  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\qtiResultReporting\model\deliveryReader;


use oat\qtiResultReporting\model\ReaderInterface;
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;
use qtism\data\AssessmentTest;

/**
 * Getting compiled test from the delivery
 *
 * Class TestReader
 * @package oat\qtiResultReporting\DeliveryReader
 */
class TestReader implements ReaderInterface
{
    /**
     * @var \core_kernel_classes_Resource
     */
    private $delivery;

    /**
     * @var array
     */
    private static $testParts = [];

    /**
     * @var array
     */
    private $testPartReaders;

    /**
     * TestReader constructor.
     * @param \core_kernel_classes_Resource $compiledDelivery
     */
    public function __construct(\core_kernel_classes_Resource $compiledDelivery)
    {
        $this->delivery = $compiledDelivery;
    }

    /**
     * @return array [TestPartReader]
     */
    public function getTestPartReaders()
    {
        if (!isset($this->testPartReaders)){
            $this->testPartReaders = [];
            $parts = $this->getTestParts();
            foreach ($parts as $key => $testPart) {
                if (!isset($this->testPartReaders[$key])) {
                    $this->testPartReaders[$key] = new TestPartReader($testPart);
                }
            }
        }

        return $this->testPartReaders;
    }

    /**
     * @return array of the TestParts
     */
    private function getTestParts()
    {
        $inputParameters = $this->getRuntimeInputParameters();
        $testFile = $inputParameters['QtiTestCompilation'];
        if (!isset(self::$testParts[$testFile])) {
            /** @var AssessmentTest $testDefinition */
            $testDefinition = \taoQtiTest_helpers_Utils::getTestDefinition($testFile);
            self::$testParts[$testFile] = $testDefinition->getComponentsByClassName('testPart');
        }

        return self::$testParts[$testFile];
    }

    /**
     * @return array
     * Example:
     * <pre>
     * array(
     *   'QtiTestCompilation' => 'http://sample/first.rdf#i14369768868163155-|http://sample/first.rdf#i1436976886612156+',
     *   'QtiTestDefinition' => 'http://sample/first.rdf#i14369752345581135'
     * )
     * </pre>
     */
    private function getRuntimeInputParameters()
    {
        $runtime = DeliveryAssemblyService::singleton()->getRuntime($this->delivery);
        $inputParameters = \tao_models_classes_service_ServiceCallHelper::getInputValues($runtime, []);

        return $inputParameters;
    }
}
