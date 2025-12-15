<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Utils_Admin {
  use Singleton_Trait;

  /**
   * Return array of adjectives for randomized username generation.
   *
   * @since 5.19.0
   * @since 5.34.0 - Moved into Utils_Admin class.
   *
   * @return string[] Array of adjectives.
   */

  public static function get_username_adjectives() : array {
    static $adjectives = array(
      'Radical', 'Tubular', 'Gnarly', 'Epic', 'Electric', 'Neon', 'Bodacious', 'Rad',
      'Totally', 'Funky', 'Wicked', 'Fresh', 'Chill', 'Groovy', 'Vibrant', 'Flashy',
      'Buff', 'Hella', 'Motor', 'Cyber', 'Pixel', 'Holo', 'Stealth', 'Synthetic',
      'Enhanced', 'Synth', 'Bio', 'Laser', 'Virtual', 'Analog', 'Mega', 'Wave', 'Solo',
      'Retro', 'Quantum', 'Robotic', 'Digital', 'Hyper', 'Punk', 'Giga', 'Electro',
      'Chrome', 'Fusion', 'Vivid', 'Stellar', 'Galactic', 'Turbo', 'Atomic', 'Cosmic',
      'Artificial', 'Kinetic', 'Binary', 'Hypersonic', 'Runic', 'Data', 'Knightly',
      'Cryonic', 'Nebular', 'Golden', 'Silver', 'Red', 'Crimson', 'Augmented', 'Vorpal',
      'Ascended', 'Serious', 'Solid', 'Master', 'Prism', 'Spinning', 'Masked', 'Hardcore',
      'Somber', 'Celestial', 'Arcane', 'Luminous', 'Ionized', 'Lunar', 'Uncanny', 'Subatomic',
      'Luminary', 'Radiant', 'Ultra', 'Starship', 'Space', 'Starlight', 'Interstellar', 'Metal',
      'Bionic', 'Machine', 'Isekai', 'Warp', 'Neo', 'Alpha', 'Power', 'Unhinged', 'Ash',
      'Savage', 'Silent', 'Screaming', 'Misty', 'Rending', 'Horny', 'Dreadful', 'Bizarre',
      'Chaotic', 'Wacky', 'Twisted', 'Manic', 'Crystal', 'Infernal', 'Ruthless', 'Grim',
      'Mortal', 'Forsaken', 'Heretical', 'Cursed', 'Blighted', 'Scarlet', 'Delightful',
      'Nuclear', 'Azure', 'Emerald', 'Amber', 'Mystic', 'Ethereal', 'Enchanted', 'Valiant',
      'Fierce', 'Obscure', 'Enigmatic'
    );

    return apply_filters( 'fictioneer_random_username_adjectives', $adjectives );
  }
}
