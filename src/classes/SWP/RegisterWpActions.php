<?php
namespace SWP;

abstract class RegisterWpActions
{
    public function registerActions()
    {
        $reflection = new \ReflectionClass(get_class($this));

        foreach ($reflection->getMethods() as $method) {
            $doc = $method->getDocComment();

            if (false !== $doc) {
                preg_match_all('#@action (.*?)\n#s', $doc, $annotations);
                $matches = $annotations[1];

                $action = array_shift($matches);

                if (null !== $action) {
                    add_action($action, array($this, $method->name));
                }
            }
        }
    }
}
