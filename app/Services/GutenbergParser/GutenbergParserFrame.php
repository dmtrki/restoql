<?php

namespace App\Services\GutenbergParser;

class GutenbergParserFrame
{
  /**
   * Full or partial block
   *
   * @since 5.0.0
   * @var GutenbergParserBlock
   */
  public $block;

  /**
   * Byte offset into document for start of parse token
   *
   * @since 5.0.0
   * @var int
   */
  public $token_start;

  /**
   * Byte length of entire parse token string
   *
   * @since 5.0.0
   * @var int
   */
  public $token_length;

  /**
   * Byte offset into document for after parse token ends
   * (used during reconstruction of stack into parse production)
   *
   * @since 5.0.0
   * @var int
   */
  public $prev_offset;

  /**
   * Byte offset into document where leading HTML before token starts
   *
   * @since 5.0.0
   * @var int
   */
  public $leading_html_start;

  /**
   * Constructor
   *
   * Will populate object properties from the provided arguments.
   *
   * @since 5.0.0
   *
   * @param GutenbergParserBlock $block              Full or partial block.
   * @param int                   $token_start        Byte offset into document for start of parse token.
   * @param int                   $token_length       Byte length of entire parse token string.
   * @param int                   $prev_offset        Byte offset into document for after parse token ends.
   * @param int                   $leading_html_start Byte offset into document where leading HTML before token starts.
   */
  function __construct($block, $token_start, $token_length, $prev_offset = null, $leading_html_start = null)
  {
    $this->block              = $block;
    $this->token_start        = $token_start;
    $this->token_length       = $token_length;
    $this->prev_offset        = isset($prev_offset) ? $prev_offset : $token_start + $token_length;
    $this->leading_html_start = $leading_html_start;
  }
}
