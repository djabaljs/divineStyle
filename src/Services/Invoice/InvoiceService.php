<?php 


namespace App\Services\Invoice;

use App\Form\PaymentTypeType;
use App\Repository\DeliveryRepository;
use App\Repository\InvoiceRepository;
use Mpdf\Mpdf;
use Symfony\Component\Asset\PathPackage;
use App\Repository\OrderProductRepository;
use App\Repository\PaymentRepository;
use App\Repository\PaymentTypeRepository;
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


    /**
     * @var paymentTypeRepository();
     */

    protected $paymentTypeRepository;





     public function __construct(OrderProductRepository $orderProductRepository, PaymentRepository $paymentRepository, DeliveryRepository $deliveryRepository, PaymentTypeRepository $paymentTypeRepository)
     {
         $this->mpdf = new Mpdf([
            'margin_left' => 20,
            'margin_right' => 20,
            'margin_top' => 50,
            'margin_bottom' => 10
         ]);

         $this->orderProductRepository = $orderProductRepository;
         $this->paymentRepository = $paymentRepository;
         $this->deliveryRepository = $deliveryRepository;
         $this->paymentTypeRepository = $paymentTypeRepository;
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
        $paymentTypes = $this->paymentTypeRepository->findAll();
        $delivery = $this->deliveryRepository->findOneBy(['order' => $invoice->getOrders()]);
        $date = date_format($invoice->getCreatedAt(), 'd/m/Y');
        $hour = date_format($invoice->getCreatedAt(), 'H:m');
        $discount = $invoice->getDiscount() != null ? $invoice->getDiscount() : 0;
        $content = '';

      
       foreach($orders as $key => $order){
         $key += 1;
         $content .= '<tr>';
         $content .= '<td>'.$key.'</td>';
         $content .= '<td>'.$order->getProducts()->getName().'</td>';
         if(is_null($order->getProducts()->getOnSaleAmount())){
            $content .= '<td>'.number_format($order->getProducts()->getSellingPrice() - $discount).'</td>';
         }else{
            $content .= '<td>'.number_format($order->getProducts()->getOnSaleAmount()).'</td>';
         }
         $content .= '<td>'.$order->getQuantity().'</td>';
         if(is_null($order->getProducts()->getOnSaleAmount()) || $order->getProducts()->getOnSaleAmount() == 0.0){
            $content .= '<td align=right>'. number_format($order->getQuantity() * $order->getProducts()->getSellingPrice() - $discount) .'</td>';
         }else{
            $content .= '<td align=right>'. number_format($order->getQuantity() * $order->getProducts()->getOnSaleAmount()) .'</td>';
         }
         $content .= '</tr>';

       }

       $deliveryAmount  = 0;
       $deliveryBody ='';

       if(!is_null($delivery)){
        $deliveryAmount = $delivery->getAmountPaid();
        $deliveryBody = '
        <tr>
        <td width="45%"><span style="font-size: 7pt; color: #555555; font-family: sans;">INFORMATIONS DE LA LIVRAISON:</span><br /><br />Nom &
             Prénoms:  '.$delivery->getRecipient().'<br />Contacts:'   .$delivery->getRecipientPhone().'<br />Commune:   '.$delivery->getAddress().'<br /></td>
        </tr>
        ';
        }

       $paymentBody = 'Mode de paiement:  ';
       foreach($paymentTypes as $paymentTypex){
          if($paymentTypex->getId() === $paymentType){
            $paymentBody .= ' <input type="checkbox" checked="checked">'.$paymentTypex->getName().' ';
          }else{
            $paymentBody .= '<input type="checkbox">'.$paymentTypex->getName().' ';
          }
       }
       $paymentBody .= '<br/><br/>
        Avance: '.number_format(($payment->getAmountPaid() + $deliveryAmount) - $discount).' <br/><br/>
        Reste à payer: '.number_format($payment->getAmount()).' <br/><br/>
        NB: Les vêtements ne sont ni repris, ni échangés
       ';

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
            <table width="100%" style="font-family: serif;" cellpadding="10">
                <tr>
                    <td width="45%"><br /><br /><span style="font-size: 7pt; color: #555555; font-family: sans;">INFORMATION DU CLIENT:</span><br /><br />Nom &
                        Prénoms:  '.$customer.'<br />Contacts:'   .$customer->getPhone().'<br />Commune:   '.$customer->getAddress().'<br /></td>
                    <td align=right><br /><br />Date: '.$date.'<br/>Heure: '.$hour.'</td>
                </tr>
                '.$deliveryBody.'
            </table>
            <br />
            <table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse; " cellpadding="8">
                <thead>
                    <tr>
                        <td width="10%">No.</td>
                        <td width="45%">Designation</td>
                        <td width="15%">P.U</td>
                        <td width="15%">Q<sup>t</sup></td>
                        <td width="20%">Total</td>
                    </tr>
                </thead>
                <tbody>
                    <!-- ITEMS HERE -->
                    '.$content.'
                    <!-- END ITEMS HERE -->
                   
                    <tr>
                        <td class="blanktotal" colspan="3" rowspan="4">
                        <br /><br /><br /><br /><br /><br /><br />  '.$paymentBody.'<br /><br /><br /><br /><br /><br />
                        </td>
                        <td class="totals">Sous-total:</td>
                        <td class="totals cost">'.number_format($invoice->getOrders()->getSaleTotal()).'</td>
                    </tr>
                    <tr>
                        <td class="totals">Remise:</td>
                        <td class="totals cost">'.number_format($discount).'</td>
                    </tr>
                    <tr>
                        <td class="totals">Livraison:</td>
                        <td class="totals cost">'.number_format($deliveryAmount).'</td>
                    </tr>
                    <tr>
                        <td class="totals"><b>TOTAL TTC:</b></td>
                        <td class="totals cost"><b>'.number_format($invoice->getAmount()).'</b></td>
                    </tr>
                </tbody>
            </table>
        </body>
        
        </html>
        ';
        // The library defines a function strcode2utf() to convert htmlentities to UTF-8 encoded text
        $this->mpdf->SetTitle('Divine Styles');
        $this->mpdf->SetProtection(array('print'));
        $this->mpdf->SetTitle("Divine Styles. - Facture");
        $this->mpdf->SetAuthor("Divine Styles.");
        $this->mpdf->SetWatermarkText("PAYÉ");
        $this->mpdf->showWatermarkText = true;
        $this->mpdf->watermark_font = 'DejaVuSansCondensed';
        $this->mpdf->watermarkTextAlpha = 0.1;
        // $this->mpdf->SetDisplayMode('fullpage');
        // $this->mpdf->SetWatermarkImage('http://localhost:8000/concept/assets/images/logo.jpg', 1,
        // '',
        // array(160,10));
        // $this->mpdf->showWatermarkImage = true;
        
        $this->mpdf->WriteHTML($this->html);
        
        $this->mpdf->Output();
    }
}