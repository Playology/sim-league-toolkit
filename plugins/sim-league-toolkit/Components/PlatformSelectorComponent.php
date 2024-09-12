<?php

  namespace SLTK\Components;

  use SLTK\Core\Constants;
  use SLTK\Database\Repositories\PlatformRepository;

  /**
   * A form input for selecting a Platform
   */
  class PlatformSelectorComponent implements FormFieldComponent {
    private const string FIELD_ID = 'sltk-platform-selector';
    private PlatformSelectorComponentConfig $config;
    private int $currentValue = Constants::DEFAULT_ID;
    private bool $isDisabled = false;

    /**
     * Creates and instance of the PlatformSelectorComponent
     *
     * @param PlatformSelectorComponentConfig|null $config Configuration settings for the component
     */
    public function __construct(PlatformSelectorComponentConfig $config = null) {
      $this->config = $config ?? new PlatformSelectorComponentConfig();
      $postedValue = sanitize_text_field($_POST[self::FIELD_ID] ?? Constants::DEFAULT_ID);

      if($postedValue !== $this->currentValue) {
        $this->currentValue = $postedValue;
      }
    }

    /**
     * @inheritDoc
     */
    public function getValue(): string {
      return $this->currentValue;
    }

    /**
     * @inheritDoc
     */
    public function render(): void {
      $platforms = PlatformRepository::listAll();

      ?>
      <select id='<?= self::FIELD_ID ?>' name='<?= self::FIELD_ID ?>'
        <?php
          disabled($this->isDisabled);
          if($this->config->submitOnSelect) {
            ?>
            onchange='this.form.submit()'
            <?php
          }
        ?>
      >
        <option value='<?= Constants::DEFAULT_ID ?>'><?= esc_html__('Please Select...', 'sim-league-toolkit') ?>.
        </option>
        <?php
          foreach($platforms as $platform) { ?>
            <option value='<?= $platform->id ?>' <?= selected($this->currentValue, $platform->id, false) ?>><?= $platform->name ?></option>
            <?php
          }
        ?>
      </select>
      <?php
    }

    /**
     * @inheritDoc
     */
    public function setValue(string $value): void {
      $this->currentValue = $value;
      $this->isDisabled = $this->config->disableOnSetValue;
    }
  }