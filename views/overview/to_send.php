<?php if ($messages) : ?>
    <?= $this->render_partial('overview/_messages.php',
        array(
            'messages' => $messages,
            'type' => 'message')
    ) ?>
<?php else : ?>
    <?= MessageBox::info(dgettext('garuda', 'Es stehen keine Nachrichten zum Versand an.')) ?>
<?php endif ?>
