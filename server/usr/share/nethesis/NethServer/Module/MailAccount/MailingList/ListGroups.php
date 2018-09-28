<?php
namespace NethServer\Module\MailAccount\MailingList;


/**
 * List groups
 *
 * @author stephane de Labrusse <stephdl@de-labrusse.fr>
 */

class ListGroups extends \Nethgui\Adapter\LazyLoaderAdapter
{
    private $platform;
    private $provider;
    private $accounts = array();
    
    private $defaults = array (
        'MailingList' => 'disabled'
    );

    private function getValue($group, $prop)
    {
        if (isset($this->accounts[$group][$prop])) {
            return $this->accounts[$group][$prop];
        } else {
            return $this->defaults[$prop];
        }
    }


    public function __construct(\Nethgui\System\PlatformInterface $platform)
    {
        $this->platform = $platform;
        $this->provider = new \NethServer\Tool\GroupProvider($this->platform);
        $this->groups = $this->provider->getGroups();
        parent::__construct(array($this, 'readGroup'));
    }

    public function flush()
    {
        $this->data = NULL;
        return $this;
    }

    public function readGroup()
    {
        $loader = new \ArrayObject();
        $this->accounts = $this->platform->getDatabase('accounts')->getAll('group');

        foreach ($this->groups as $group => $values) {

            $loader[$group]['Group'] = $group;
            foreach (array_keys($this->defaults) as $prop) {
                $loader[$group][$prop] = $this->getValue($group, $prop);
            }
        }

        return $loader;
    }
}
