<?php

namespace Drupal\dcb\Encoder;

use Symfony\Component\Serializer\Encoder\JsonEncoder as SymfonyJsonEncoder;

/**
 * Encodes JSON API data.
 *
 * Simply respond to application/vnd.api+json format requests using encoder.
 */
class DcbJsonEncoder extends SymfonyJsonEncoder {

  /**
   * The formats that this Encoder supports.
   *
   * @var string
   */
  protected $format = 'dcb_json';

  /**
   * {@inheritdoc}
   */
  public function supportsEncoding($format) {
    list($data_format,) = explode(':', $format);
    return $data_format == $this->format;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsDecoding($format) {
    return FALSE;
  }

}
