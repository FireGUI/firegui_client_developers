<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

<?php

//debug($field);
echo $label; ?>
<br />
<div class="<?php echo $class ?>">

    <input type="hidden" class="default" id="js_field_<?php echo $field['fields_id']; ?>"
        name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>"
        data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />




    <div class="canvas_ct">


        <div class='signature_pad_ct hide'>


            <div id='firma_cliente'>
                <div class='firma'>
                    <div id='firma-cliente' class='signature-pad'>
                        <div class='signature-pad--body'>
                            <canvas id='canvas-cliente'></canvas>
                        </div>
                    </div>
                </div>


            </div>



        </div>
    </div>

    <div class="alert alert-info orientation-alert">LE FIRME FUNZIONANO SOLAMENTE IN MODALITA' LANDSCAPE</div>


    <style>
        .signature_pad_ct *,
        .signature_pad_ct *::before,
        .signature_pad_ct *::after {
            box-sizing: border-box;
        }

        .nav-justified-mobile>li {
            width: 49%;
            justify-content: space-between;
            text-align: center;
        }

        .firma {
            width: 100%;
            /*height: 100vh;*/
            height: 250px !important;
            /*calc(95vh - (100px + 150px));*/
            background-color: pink;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
        }

        .signature-pad {
            position: relative;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            font-size: 10px;
            width: 100%;
            height: 100%;
            /*max-width: 700px;*/
            /*max-height: 460px;*/
            border: 1px solid #e8e8e8;
            background-color: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.27), 0 0 40px rgba(0, 0, 0, 0.08) inset;
            border-radius: 4px;
            /*padding: 16px;*/
        }

        .signature-pad::before,
        .signature-pad::after {
            position: absolute;
            z-index: -1;
            content: "";
            width: 100%;
            height: 100%;
            bottom: 10px;
            background: transparent;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .signature-pad::before {
            /*left: 20px;*/
            -webkit-transform: skew(-3deg) rotate(-3deg);
            transform: skew(-3deg) rotate(-3deg);
        }

        .signature-pad::after {
            /*right: 20px;*/
            -webkit-transform: skew(3deg) rotate(3deg);
            transform: skew(3deg) rotate(3deg);
        }

        .signature-pad--body {
            position: relative;
            -webkit-box-flex: 1;
            -ms-flex: 1;
            flex: 1;
            border: 1px solid #f4f4f4;
        }

        .signature-pad--body canvas {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            border-radius: 4px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.02) inset;
        }

        #btn-salva {
            margin-top: 15px !important;
        }

        @media (orientation: landscape) {
            .layout_box {
                margin-top: 0;
                padding-top: 0;
            }

            .signature-tab a {
                padding: 3px 7px !important;
            }

            #btn-salva {
                padding: 3px 9px !important;
                margin-top: 15px !important;
                font-size: 16px !important;
            }

            .tab-content {
                padding-top: 15px !important;

            }

            .firma {
                height: 250px;
                /*calc(98vh - (100px + 150px)) !important;*/
            }

            .canvas_ct {
                display: block;
            }

            .orientation-alert {
                display: none;
            }
        }

        @media screen and (orientation:portrait) {
            .canvas_ct {
                display: none;
            }

            .orientation-alert {
                display: block;
            }
        }
    </style>

    <script>
        // Adjust canvas coordinate space taking into account pixel ratio,
        // to make it look crisp on mobile devices.
        // This also causes canvas to be cleared.
        function resizeCanvas(signaturePad, canvas) {
            // console.log('trigger resize')

            // When zoomed out to less than 100%, for some very strange reason,
            // some browsers report devicePixelRatio as less than 1
            // and only part of the canvas is cleared then.
            const ratio = Math.max(window.devicePixelRatio || 1, 1);

            // console.log(canvas)

            // This part causes the canvas to be cleared
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;

            // console.log(canvas)

            canvas.getContext("2d").scale(ratio, ratio);

            // If you want to keep the drawing on resize instead of clearing it you can reset the data.
            signaturePad.fromData(signaturePad.toData());
        }

        function initSignature(element_id) {
            try {
                console.log('init signature');

                const wrapper = document.getElementById(element_id);
                const canvas = wrapper.querySelector("canvas");
                const signaturePad = new SignaturePad(canvas);

                function updateSignature() {
                    let firma = signaturePad.toDataURL();
                    firma = firma.replace('data:image/png;base64,', '');
                    $('#js_field_<?php echo $field['fields_id']; ?>').val(firma);
                }

                // Aggiungi eventi sia per mousemove che per touchmove
                canvas.addEventListener('mousemove', updateSignature);
                canvas.addEventListener('touchmove', updateSignature);

                // Aggiungi l'evento resize/orientationchange come avevi già fatto
                $(window).on('resize orientationchange', function() {
                    resizeCanvas(signaturePad, canvas)
                }).trigger('resize');

                // Chiara la firma se necessario
                // const clearButton = wrapper.querySelector("[data-action=clear]");
                // clearButton.addEventListener("click", () => {
                //     signaturePad.clear();
                // });

                return signaturePad;
            } catch (e) {
                console.log(e);
                setTimeout(function () { initSignature(element_id); }, 500);
            }
        }

        $(() => {
            $('.canvas-loading').addClass('hide');
            $('.signature_pad_ct').removeClass('hide');

            const canvas_cliente = initSignature('firma-cliente');
            console.log(canvas_cliente);




            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                console.log('tab changed, triggering fake window resize')

                $(window).trigger('resize');
            });
        });





    </script>
</div>
<?php echo $help; ?>