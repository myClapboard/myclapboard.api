<?php

/**
 * This file belongs to myClapboard.
 * The source code of application includes a LICENSE file
 * with all information about license.
 *
 * @author benatespina <benatespina@gmail.com>
 * @author gorkalaucirica <gorka.lauzirika@gmail.com>
 */

namespace spec\Myclapboard\AwardBundle\Model;

use Myclapboard\AwardBundle\Entity\AwardTranslation;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class AwardSpec.
 *
 * @package spec\Myclapboard\AwardBundle\Model
 */
class AwardSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Myclapboard\AwardBundle\Model\Award');
    }

    function it_implements_award_interface()
    {
        $this->shouldImplement('Myclapboard\AwardBundle\Model\Interfaces\AwardInterface');
    }

    function its_name_is_mutable()
    {
        $this->setName('Oscar')->shouldReturn($this);
        $this->getName()->shouldReturn('Oscar');
    }

    function its_name_translations_be_mutable()
    {
        $translation = new AwardTranslation('es', 'name', 'spanish-name-translation');

        $this->getTranslations()->shouldHaveCount(0);
        $this->addTranslation($translation);
        $this->getTranslations()->shouldHaveCount(1);

        // If array of translations contains translation, it does not add it again
        $this->addTranslation($translation);
        $this->getTranslations()->shouldHaveCount(1);

        $this->removeTranslation($translation);
        $this->getTranslations()->shouldHaveCount(0);
    }
}
