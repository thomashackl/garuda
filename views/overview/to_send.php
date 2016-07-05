<?php if ($messages) : ?>
    <?= $this->render_partial('overview/_messages.php',
        array(
            'messages' => $messages,
            'title' => dgettext('garudaplugin', 'Zum Versand anstehende, aber noch nicht verschickte Nachrichten'),
            'type' => 'message')
    ) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('garudaplugin', 'Es stehen keine Nachrichten zum Versand an.')) ?>
<?php endif ?>
