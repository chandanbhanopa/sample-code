<?php
/**
 * Helm
 *
 * Main regitration file
 *
 * This source file is subject to the Helm.
 * This is used to register the module
 *
 * PHP version 8.2
 *
 * @category  ProductKit
 * @package   Helm
 * @author    Chandan Bhanopa <cbhanopa@helm.com>
 * @copyright 2024 Helm
 * @license   https://www.helm.com helm
 * @link      https://www.helm.com
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Helm_ProductKit',
    __DIR__
);
