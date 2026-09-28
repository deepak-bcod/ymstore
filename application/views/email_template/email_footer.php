<?php
$site_lang = $this->session->userdata('site_lang');

$footer_message = ($site_lang === 'french')
    ? 'Merci de soutenir les entreprises locales lorsque vous faites vos achats sur Yellow Markets'
    : 'Thank you for supporting local business when shopping on Yellow Markets';
?>

<tr>
    <td style="background:#000000; padding:0 15px 40px;">
        <table width="100%" cellspacing="0" cellpadding="0"
               style="border-collapse:collapse; background:#000000;">
            <tr>
                <td class="blue-sec"
                    style="text-align:center; padding:20px; color:#ffffff; font-size:16px; background:#000000;">
                    <?php echo $footer_message; ?>
                </td>
            </tr>
        </table>
    </td>
</tr>

</table>
</body>
</html>