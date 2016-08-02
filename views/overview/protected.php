<?php if ($messages) : ?>
    <?= $this->render_partial('overview/_messages.php',
        array(
            'messages' => $messages,
            'title' => dgettext('garudaplugin',
                'Gesch�tzte Nachrichten, die nach erfolgreichem Versand nicht gel�scht werden d�rfen'),
            'type' => 'message')
    ) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('garudaplugin', 'Es sind keine gesch�tzten Nachrichten vorhanden.')) ?>
<?php endif ?>
