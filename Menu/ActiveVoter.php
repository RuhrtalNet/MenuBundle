<?php
/**
 * This file is part of the RuhrtalNet\MenuBundle.
 *
 * @version $Revision$
 */

namespace RuhrtalNet\MenuBundle\Menu;

use Knp\Menu\ItemInterface;
use Knp\Menu\Matcher\Voter\VoterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 */
class ActiveVoter implements VoterInterface
{
    /**
     * Checks whether an item is current.
     *
     * If the voter is not able to determine a result,
     * it should return null to let other voters do the job.
     *
     * @param \Knp\Menu\ItemInterface $item
     * @return boolean|null
     */
    public function matchItem(ItemInterface $item)
    {
        return $item->getExtra('active');
    }
}
