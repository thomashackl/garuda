<?php if ($messages) : ?>
    <?= $this->render_partial('overview/_messages.php',
        array(
            'messages' => $messages,
            'title' => dgettext('garudaplugin',
                'Geschützte Nachrichten, die nach erfolgreichem Versand nicht gelöscht werden dürfen'),
            'type' => 'message')
    ) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('garudaplugin', 'Es sind keine geschützten Nachrichten vorhanden.')) ?>
<?php endif ?>
