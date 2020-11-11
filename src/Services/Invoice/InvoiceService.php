<?php 


namespace App\Services\Invoice;

use App\Repository\DeliveryRepository;
use App\Repository\InvoiceRepository;
use Mpdf\Mpdf;
use Symfony\Component\Asset\PathPackage;
use App\Repository\OrderProductRepository;
use App\Repository\PaymentRepository;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;


class InvoiceService{

    /**
     * @var mpdf
     */
    protected $mpdf;

    /**
     * @var html
     */
    protected $html;


    /**
     * @var orderProductRepository();
     */

    protected $orderProductRepository;


    /**
     * @var paymentRepository;
     */

    protected $paymentRepository;


    /**
     * @var deliveryRepository();
     */

    protected $deliveryRepository;




     public function __construct(OrderProductRepository $orderProductRepository, PaymentRepository $paymentRepository, DeliveryRepository $deliveryRepository)
     {
         $this->mpdf = new Mpdf([
            'margin_left' => 20,
            'margin_right' => 15,
            'margin_top' => 38,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10
         ]);

         $this->orderProductRepository = $orderProductRepository;
         $this->paymentRepository = $paymentRepository;
         $this->deliveryRepository = $deliveryRepository;
     }


    /**
     * @method generateInvoice();
     */
 
    public function generateInvoice($invoice, $logo)
    {
        $orders = $this->orderProductRepository->findOrderProducts($invoice->getOrders());
        $shop = $invoice->getOrders()->getShop();
        $customer = $invoice->getOrders()->getCustomer();
        $payment = $this->paymentRepository->findOneBy(['invoice' => $invoice]);
        $paymentType = $payment->getPaymentType()->getId();
        $delivery = $this->deliveryRepository->findOneBy(['order' => $invoice->getOrders()]);
        $date = date_format($invoice->getCreatedAt(), 'd/m/Y');
        $hour = date_format($invoice->getCreatedAt(), 'H:m');

        $content = '';

       foreach($orders as $key => $order){
         $key += 1;
         $content .= '<tr>';
         $content .= '<td>'.$key.'</td>';
         $content .= '<td>'.$order->getProducts()->getName().'</td>';
         $content .= '<td>'.number_format($order->getProducts()->getSellingPrice()).'</td>';
         $content .= '<td>'.$order->getQuantity().'</td>';
         $content .= '<td>'. number_format($order->getQuantity() * $order->getProducts()->getSellingPrice()) .'</td>';
         $content .= '</tr>';

       }

     

       $deliveryBody = '';
       if(!is_null($delivery)){
           $deliveryBody = '
           <tr>
                <td class="totals">Livraison:</td>
                <td class="totals cost">'.number_format($delivery->getAmountPaid()).'</td>
          </tr>
           ';
       }

       $paymentBody = '';
       if($paymentType === 4){
           $paymentBody ='
                Mode de paiement: <input type="checkbox" checked="checked">Espèce <input type="checkbox">Carte de crédit <input
                type="checkbox">Chèque<br/><br/>
                Avance: '.number_format($payment->getAmountPaid()).' <br/><br/>
                Reste à payer: '.number_format($payment->getAmount()).' <br/><br/>
                NB: Les vêtements ne sont ni repris, ni échangés
           ';
       }elseif($paymentType === 5){
            $paymentBody = '
                Mode de paiement: <input type="checkbox">Espèce <input type="checkbox">Carte de crédit <input
                type="checkbox" checked="checked">Chèque<br/><br/>
                Avance: '.number_format($payment->getAmountPaid()).' <br/><br/>
                Reste à payer: '.number_format($payment->getAmount()).' <br/><br/>
                NB: Les vêtements ne sont ni repris, ni échangés
        ';
       }elseif($paymentType === 6){
        $paymentBody = '
            Mode de paiement: <input type="checkbox">Espèce <input type="checkbox" checked="checked">Carte de crédit <input
            type="checkbox">Chèque<br/><br/>
            Avance: '.number_format($payment->getAmountPaid()).' <br/><br/>
                Reste à payer: '.number_format($payment->getAmount()).' <br/><br/>
                NB: Les vêtements ne sont ni repris, ni échangés
            ';
        }

       $this->html = '
        <html>
        <head>
            <style>
                body {
                    font-family: sans-serif;
                    font-size: 10pt;
                }
        
                p {
                    margin: 0pt;
                }
        
                table.items {
                    border: 0.1mm solid #000000;
                }
        
                td {
                    vertical-align: top;
                }
        
                .items td {
                    border-left: 0.1mm solid #000000;
                    border-right: 0.1mm solid #000000;
                }
        
                table thead td {
                    background-color: #EEEEEE;
                    text-align: center;
                    border: 0.1mm solid #000000;
                    font-variant: small-caps;
                }
        
                .items td.blanktotal {
                    background-color: #EEEEEE;
                    border: 0.1mm solid #000000;
                    background-color: #FFFFFF;
                    border: 0mm none #000000;
                    border-top: 0.1mm solid #000000;
                    border-right: 0.1mm solid #000000;
                }
        
                .items td.totals {
                    text-align: right;
                    border: 0.1mm solid #000000;
                }
        
                .items td.cost {
                    text-align: "."center;
                }
            </style>
        </head>
        
        <body>
            <!--mpdf
        <htmlpageheader name="myheader">
        <table width="100%"><tr>
        <td width="50%" style="color:#0000BB; "><span style="font-weight: bold; font-size: 14pt;">Divine Styles.</span><br />'.$shop->getAddress().'<br /><span style="font-family:dejavusanscondensed;">&#9742;</span>'.$shop->getPhone().'</td>
        <td width="50%" style="text-align: right;">Facture No.<br /><span style="font-weight: bold; font-size: 12pt;">'.$invoice->getInvoiceNumber().'</span></td>
        </tr></table>
        </htmlpageheader>
        <htmlpagefooter name="myfooter">
        <div style="border-top: 1px solid #000000; font-size: 9pt; text-align: center; padding-top: 3mm; ">
        Page {PAGENO} of {nb}
        </div>
        </htmlpagefooter>
        <sethtmlpageheader name="myheader" value="on" show-this-page="1" />
        <sethtmlpagefooter name="myfooter" value="on" />
        mpdf-->
            <div style="text-align: right">Date: '.$date.'</div>
            <div style="text-align: right">Heure: '.$hour.'</div>
            <table width="100%" style="font-family: serif;" cellpadding="10">
                <tr>
                    <td width="45%"></td>
                    <td width="10%">&nbsp;</td>
                    <td width="45%"><span style="font-size: 7pt; color: #555555; font-family: sans-serif;"></span><br /><br />Nom &
                        Prénoms: '.$customer.'<br />Contacts:' .$customer->getPhone().'<br />Commune: '.$customer->getAddress().'<br /></td>
                </tr>
            </table>
            <br />
            <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
                <thead>
                    <tr>
                        <td width="15%">No.</td>
                        <td width="10%">Designation</td>
                        <td width="45%">P.U</td>
                        <td width="15%">Q<sup>t</sup></td>
                        <td width="15%">Total</td>
                    </tr>
                </thead>
                <tbody>
                    <!-- ITEMS HERE -->
                    '.$content.'
                    <!-- END ITEMS HERE -->
                   
                    <tr>
                        <td class="blanktotal" colspan="3" rowspan="6">
                       '.$paymentBody.'
                        </td>
                        <td class="totals">Sous-total:</td>
                        <td class="totals cost">'.number_format($invoice->getOrders()->getSaleTotal()).'</td>
                    </tr>
                    <tr>
                        <td class="totals">Taxe:</td>
                        <td class="totals cost">'.number_format(0).'</td>
                    </tr>
                    '.$deliveryBody.'
                    <tr>
                        <td class="totals"><b>TOTAL TTC:</b></td>
                        <td class="totals cost"><b>'.number_format($invoice->getAmount()).'</b></td>
                    </tr>
                </tbody>
            </table>
        </body>
        
        </html>
        ';

        $this->mpdf->SetProtection(array('print'));
        $this->mpdf->SetTitle("Divine styles. - Facture");
        $this->mpdf->SetAuthor("Acme Trading Co.");
        $this->mpdf->SetWatermarkText("Payé");
        $this->mpdf->showWatermarkText = true;
        $this->mpdf->watermark_font = 'DejaVuSansCondensed';
        $this->mpdf->watermarkTextAlpha = 0.1;
        $this->mpdf->SetDisplayMode('fullpage');
        // $this->mpdf->SetWatermarkImage($logo);
        // $this->mpdf->showWatermarkImage = true;
        
        $this->mpdf->WriteHTML($this->html);
        
        $this->mpdf->Output();
    }
}