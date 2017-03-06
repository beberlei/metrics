<?php
/**
 * Beberlei Metrics.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Beberlei\Metrics\Collector;

/**
 * TaggableCollector interface.
 */
interface TaggableCollector
{
    /**
     * Sets a metrics tags.
     *
     * @param array $tags
     */
    public function setTags($tags);
}
