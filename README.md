# value-object-form-bundle

## Usage

#### The Kebab model
```php
class Kebab
{
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function getIdentity()
    {
        return [
            'name' => $this->name,
        ];
    }

    public function setIdentity(Identity $identity)
    {
        $this->name = $identity->name;
        // ...
    }
}
```

#### The "POPO" Kebab identifier
```php
class Identity
{
    public $name;
    
    // ...
}
```

#### The Kebab identifier form
```php
use AppBundle\Form\Type\KebabIdentifierType;
use AppBundle\Entity\Kebab;
use AppBundle\Entity\Kebab\Identity;

$this->createForm(KebabIdentifierType::CLASS, $kebab, [
    'object_accessor' => 'getIdentity',
    'object_mutator' => 'setIdentity',
    'value_object_class' => Identity::class
]);

```
