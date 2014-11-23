<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Silvestra\Component\Media\Form\Type;

use Silvestra\Component\Media\ImageConfig;
use Silvestra\Component\Media\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 11/23/14 3:57 PM
 */
class ImageType extends AbstractType
{
    /**
     * @var ImageConfig
     */
    private $config;

    /**
     * @var string
     */
    private $imageClass;

    /**
     * Constructor.
     *
     * @param string $imageClass
     * @param ImageConfig $config
     */
    public function __construct($imageClass, ImageConfig $config)
    {
        $this->imageClass = $imageClass;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cropperCoordinates', 'hidden');

        $builder->add('originalPath', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $settings = array(
            'types' => $options['types'],
            'max_file_size' => $options['max_file_size'],
            'max_height' => $options['max_height'],
            'max_width' => $options['max_width'],
            'min_height' => $options['min_height'],
            'min_width' => $options['min_width'],
            'resize_strategy' => $options['resize_strategy'],
            'cropper_enabled' => $options['cropper_enabled'],
            'cropper_coordinates' => $options['cropper_coordinates'],
        );

        $view->vars['settings'] = json_encode($settings);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->imageClass,
                'label' => false,

                'types' => $this->config->getAvailableMimeTypes(),
                'max_file_size' => $this->config->getMaxFileSize(),
                'max_height' => $this->config->getMaxHeight(),
                'max_width' => $this->config->getMaxWidth(),
                'min_height' => $this->config->getMinHeight(),
                'min_width' => $this->config->getMinWidth(),
                'resize_strategy' => $this->config->getDefaultResizeStrategy(),
                'cropper_enabled' => $this->config->isDefaultCropperEnabled(),
                'cropper_coordinates' => function (Options $options) {
                    return array(
                        'x1' => 0,
                        'y1' => 0,
                        'x2' => $options['max_width'],
                        'y2' => $options['max_height'],
                    );
                }
            )
        );

        $config = $this->config;

        $resolver->setAllowedValues(
            array(
                'types' => function ($types) use ($config) {
                    foreach ($types as $type) {
                        if (!in_array($type, $config->getAvailableMimeTypes())) {
                            return false;
                        }
                    }

                    return true;
                },
                'max_file_size' => function ($maxFileSize) use ($config) {
                    return ($config->getMaxFileSize() >= $maxFileSize);
                },
                'max_height' => function ($maxHeight) use ($config) {
                    return ($config->getMaxHeight() >= $maxHeight);
                },
                'max_width' => function ($maxWidth) use ($config) {
                    return ($config->getMaxWidth() >= $maxWidth);
                },
                'min_height' => function ($minHeight) use ($config) {
                    return ($config->getMinHeight() <= $minHeight);
                },
                'min_width' => function ($minWidth) use ($config) {
                    return ($config->getMinWidth() <= $minWidth);
                },
                'resize_strategy' => function ($resizeStrategy) {
                    return in_array($resizeStrategy, Media::getResizeStrategies());
                },
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'silvestra_media_image';
    }
}
