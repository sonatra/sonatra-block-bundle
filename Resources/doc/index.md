Getting Started
===============

## Prerequisites

This version of the bundle requires Symfony 3.

## Installation

Installation is a quick, 2 step process:

1. Download the bundle using composer
2. Enable the bundle
3. Configure the bundle (optional)

### Step 1: Download the bundle using composer

Tell composer to download the bundle by running the command:

```bash
$ composer require fxp/block-bundle
```

Composer will install the bundle to your project's `vendor/fxp` directory.

### Step 2: Enable the bundle

Enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Fxp\Bundle\BlockBundle\FxpBlockBundle(),
    );
}
```

### Step 3: Configure the bundle (optional)

You can override the default configuration adding `fxp_block` tree in `app/config/config.yml`.
For see the reference of Fxp Block Configuration, execute command:

```bash
$ php app/console config:dump-reference FxpBlockBundle 
```

### Next Steps

Now that you have completed the basic installation and configuration of the
Fxp BlockBundle, you are ready to learn about usages of the bundle.

The following documents are available:

- [Enjoy!](usage.md)
