<?php if ($messages) : ?>
    <?= $this->render_partial('overview/_messages.php',
        array(
            'messages' => $messages,
            'type' => 'message')
    ) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('garudaplugin', 'Es sind keine geschützten Nachrichten vorhanden.')) ?>
<?php endif ?>
