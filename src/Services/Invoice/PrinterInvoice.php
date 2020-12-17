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
use App\Repository\ProductVariationRepository;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;


class PrinterInvoice{

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
     * @var deliveryRepository;
     */

    protected $deliveryRepository;


    /**
     * @var paymentTypeRepository;
     */

    protected $paymentTypeRepository;

      /**
     * @var productVariation;
     */

    protected $productVariation;





     public function __construct(OrderProductRepository $orderProductRepository, PaymentRepository $paymentRepository, DeliveryRepository $deliveryRepository, PaymentTypeRepository $paymentTypeRepository, ProductVariationRepository $productVariationRepository)
     {
         $this->mpdf = new Mpdf();

         $this->orderProductRepository = $orderProductRepository;
         $this->paymentRepository = $paymentRepository;
         $this->deliveryRepository = $deliveryRepository;
         $this->paymentTypeRepository = $paymentTypeRepository;
         $this->productVariation = $this->productVariation;
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
        $date = date_format($invoice->getCreatedAt(), 'd/m/Y H:m');
        $hour = date_format($invoice->getCreatedAt(), 'H:m');
        $discount = $invoice->getDiscount() != null ? $invoice->getDiscount() : 0;
        
       $content = "";
        
       foreach($orders as $key => $order){
        $content .= "
        <hr>
        <table>
        ";

        $variationHTML = '';
            if(!is_null($order->getProducts())){
                
                    if($order->getProducts()->getIsVariable()){

                        foreach($order->getProducts()->getProductVariations() as $variation){
                            $variationHTML .='<tr>';
                                $variationHTML .='<td>Taille:</td>';
                                $variationHTML .='<td>'.$variation->getLength().'</td>';
                            $variationHTML .='</tr>';
                            $variationHTML .='<tr>';
                                $variationHTML .='<td>Couleur:</td>';
                                $variationHTML .='<td>'.$variation->getColor().'</td>';
                            $variationHTML .='</tr>';

                        }
                    }else{
                    $variationHTML .='<tr>';
                        $variationHTML .='<td>Taille:</td>';
                        $variationHTML .='<td>//</td>';
                    $variationHTML .='</tr>';
                        $variationHTML .='<td>Couleur:</td>';
                        $variationHTML .='<td>//</td>';
                    $variationHTML .='</tr>';
                
                }  
        
            }
       
         $key += 1;
         $content .= '<tr>';
            $content .= '<td>Produit:</td>';
            $content .= '<td>'.$order->getProducts()->getName().'</td>';
         $content .= '</tr>';
        
       
         $content.= $variationHTML;

         $content .= '<tr>';
         $content .= '<td>Qt:</td>';
         $content .= '<td>'.$order->getQuantity().'</td>';
         $content .= '</tr>';

         if(is_null($order->getProducts()->getOnSaleAmount()) || $order->getProducts()->getOnSaleAmount() == 0.0){
            $content .= '<tr>';
                $content .= '<td>Pu:</td>';
                $content .= '<td>'.number_format($order->getProducts()->getSellingPrice()).'</td>';
            $content .= '</tr>';
         }else{

            $content .= '<tr>';
                $content .= '<td>Pu:</td>';
                $content .= '<td>'.number_format($order->getProducts()->getOnSaleAmount()).'</td>';
            $content .= '</tr>';
         }
        //  if(is_null($order->getProducts()->getOnSaleAmount()) || $order->getProducts()->getOnSaleAmount() == 0.0){
        //     $content .= '<td>'. number_format($order->getQuantity() * $order->getProducts()->getSellingPrice()) .'</td>';
        //  }else{
        //     $content .= '<td>'. number_format($order->getQuantity() * $order->getProducts()->getOnSaleAmount()) .'</td>';
        //  }
         $content .= '</table>';

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
            $paymentBody .= '<br/> <input type="checkbox" checked="checked">'.$paymentTypex->getName().' ';
          }else{
            $paymentBody .= '<input type="checkbox">'.$paymentTypex->getName().' ';
          }
       }
    //    $paymentBody .= '<br/><br/>
    //     Avance: '.number_format($payment->getAmountPaid() + $deliveryAmount).' <br/>
    //     Reste à payer: '.number_format($payment->getAmount()).' <br/>
    //    ';

       $this->html = '
       <!DOCTYPE html>
       <html lang="ar">
       <!-- <html lang="ar"> for arabic only -->
       <head>
           <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
       
           <title>Divine Styles</title>
           <style>
               @media print {
                   table, div{
                       font-size: 12px;
                   }
                   @page {
                       margin: 0 auto; /* imprtant to logo margin */
                       sheet-size: 160px 210mm; /* imprtant to set paper size */
                   }
                   html {
                       direction: rtl;
                   }
                   html,body{margin:0;padding:0}
                   #printContainer {
                       width: 200px;
                       margin: auto;
                       /*padding: 10px;*/
                       /*border: 2px dotted #000;*/
                       text-align: justify;
                   }
       
                  .text-center{text-align: center;}
               }
           </style>
       </head>
       <body onload="window.print();">
       
       <div >
          <img src="https://divinestylestock.net/concept/assets/images/logo.jpg" width="50%" style="margin-left:50%%">
          <h2 id="slogan" style="margin-top:0" class="text-center">Divine Styles</h2>

           <table>
                <tr>
                    <td><span style="font-weight: bold; text-transform: uppercase; font-size: 12pt;">'.$shop->getAddress().'<br /><span style="font-family:dejavusanscondensed;">&#9742;</span>'.$shop->getPhone().'</td>
                    <br/> <br/>
               </tr>
               <tr>
                    <td colspan="2" style="font-size: 11pt;">Les vêtements ne sont ni repris, ni échangés</td>
                    <br/> <br/>
               </tr>
               <tr>
                   <td>Facture N°</td>
                   <td><b>'.$invoice->getInvoiceNumber().'</b></td>
               </tr>
               <tr>
                   <td>Date</td>
                   <td><b>'.$date.'<br></b></td>
               </tr>
       
               <tr>
                   <td>Client</td>
                   <td><b>'.$customer.'</b></td>
               </tr>
               '.$deliveryBody.'
           </table>
       
           '.$content.'
           <hr>
           <table>
           <tr>
           <td>Sous-total:</td>
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
           <td class="totals cost"><b>'.number_format($invoice->getAmount() + $deliveryAmount).'</b></td>
           </tr>
           </table>
           <br/>
       '.$paymentBody.'

       </div>
       </body>
       </html>
        ';

        //   ini_set('memory_limit', '256M');
        // load library
    //     $this->load->library('pdf');
    //     $pdf = $this->pdf->load();
    //     // retrieve data from model or just static date
    //     $data['title'] = "items";
    //     $pdf->allow_charset_conversion=true;  // Set by default to TRUE
    //     $pdf->charset_in='UTF-8';
    //  //   $pdf->SetDirectionality('rtl'); // Set lang direction for rtl lang
    //     $pdf->autoLangToFont = true;
    //     $html = $this->load->view('content/mpdf', $data, true);
    //     // render the view into HTML
    //     $pdf->WriteHTML($html);
    //     // write the HTML into the PDF
    //     $output = 'itemreport' . date('Y_m_d_H_i_s') . '_.pdf';
    //     $pdf->Output("$output", 'I');
        // save to file because we can exit();
        // The library defines a function strcode2utf() to convert htmlentities to UTF-8 encoded text
        $this->mpdf->SetTitle('Divine Styles');
        $this->mpdf->SetProtection(array('print'));
        $this->mpdf->SetTitle("Divine Styles. - Facture");
        $this->mpdf->SetAuthor("Divine Styles.");
        // $this->mpdf->SetWatermarkText("PAYÉ");
        // $this->mpdf->showWatermarkText = true;
        // $this->mpdf->watermark_font = 'DejaVuSansCondensed';
        // $this->mpdf->watermarkTextAlpha = 0.1;
        // $this->mpdf->SetDisplayMode('fullpage');
        // $this->mpdf->SetWatermarkImage('http://localhost:8000/concept/assets/images/logo.jpg', 1,
        // '',
        // array(160,10));
        // $this->mpdf->showWatermarkImage = true;
        
        $this->mpdf->WriteHTML($this->html);
        
        $this->mpdf->Output();
    }
}