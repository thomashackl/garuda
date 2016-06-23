<?php if ($messages) : ?>
    <?= $this->render_partial('overview/_messages.php',
        array(
            'messages' => $messages,
            'title' => dgettext('garudaplugin', 'Nachrichten, die gerade zum Versand verarbeitet werden'))
    ) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('garudaplugin', 'Es werden gerade keine Nachrichten verarbeitet.')) ?>
<?php endif ?>
