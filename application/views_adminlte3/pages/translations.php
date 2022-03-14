<?php

$strings = [];

?>

<section class="content-header">
    <h1 class="clearfix"><?php e('Translations Tool'); ?></h1>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border">
            <?php e('Settings'); ?>
        </div>

        <div class="box-body">
            <div class="row">
                <div class="col-md-2">
                    <strong><?php e('Your language is: '); ?></strong>
                    <?php echo $data['settings']['languages_name']; ?>
                </div>
            </div>
        </div>

    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?php e('Missing translations'); ?></h3>
        </div>

        <?php if (!empty($data['log_file_error'])) : ?>
            <p>Log file not found, pleaase check your config.php and enable <code>log_threshold</code> level to: 4;</p>
        <?php endif; ?>

        <table class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th><?php e('English Word'); ?></th>
                    <?php foreach ($data['languages'] as $language) : ?>
                        <th><?php echo $language['languages_name']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($data['log_lines'] as $line) : ?>

                    <?php
                    if (empty($line))
                        continue;
                    if (strpos($line, 'Could not find the language line') === false)
                        continue;

                    $string = $line;
                    $expl = explode(' - ', $string);
                    $type = array_shift($expl);
                    $string = implode(' - ', $expl);
                    $expl2 = explode(' --> ', $string);
                    $date = array_shift($expl2);
                    $message = implode(' --> ', $expl2);
                    $_string = str_replace("Could not find the language line ", "", $message);
                    $string = str_replace('"', "", $_string);

                    // Remove duplicates
                    if (in_array($string, $strings))
                        continue;

                    $strings[] = $string;

                    // Limit to 200 words
                    if (count($strings) > 200)
                        continue;
                    ?>
                    <tr>
                        <td><?php echo $string; ?></td>
                        <?php foreach ($data['languages'] as $language) : ?>
                            <td><input type="text" name="new_word" /></td>
                        <?php endforeach; ?>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>


    </div>
</section>