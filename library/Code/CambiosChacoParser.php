<?php

include_once('Amedia/simple_html_dom.php');

/**
*
*/
class Code_CambiosChacoParser
{
    public function __construct()
    {
    }

    /*
        agregar campo de fecha de cotizacion o usar un maestro y detalle por fecha.
        return array(array('moneda' => '', 'compra' => '', 'venta' => ''))
     */
    public function ejecutar($fileURL)
    {
        // http://stackoverflow.com/questions/272361/how-can-i-handle-the-warning-of-file-get-contents-function-in-php

        // $html = file_get_html('http://www.cambioschaco.com.py/php/chaco_cotizaciones_nuevo.php');
        $html = @file_get_html($fileURL);

        if ($html === FALSE)
        {
            return FALSE;
        }

        $cont = 0;
        $patronNombre = '/;(\s*\w\s*)*</';
        $patronNumero = '/\d+(\.)?\d+\,00/';

        $cotizaciones = array();

        foreach ($html->find('table table tr') as $element)
        {
            $cotizacion = array();

            if (strpos($element->innertext, "paises") !== false)
            {
                $cont++;

                $paisNombre = "";
                $valorVenta = "";
                $valorCompra = "";

                $paisNombreElemento = $element->children(1);

                // las filas de cabecera tienen un solo children td, entonces continuar
                if ($paisNombreElemento === null)
                {
                    continue;
                }

                // extraer el nombre del pais de la cotizacion si existiese
                preg_match($patronNombre, $paisNombreElemento->innertext, $coincidencias, PREG_OFFSET_CAPTURE);

                // si hay entonces quitar el nombre del pais
                if (count($coincidencias) > 0)
                {
                    // obtener el nombre del pais
                    foreach ($coincidencias as $encontrados)
                    {
                        $paisNombre = $encontrados[0];
                        $paisNombre = str_replace(";", "", $paisNombre);
                        $paisNombre = str_replace("<", "", $paisNombre);
                        $paisNombre = trim($paisNombre);
                        // echo '<pre>'; var_dump($paisNombre); echo '</pre>';
                        break; // solo se obtiene el primer valor de coincidencia
                    }
                }

                if ($paisNombre === '')
                {
                    continue;
                }

                $cotizacion[Api_Model_Entidad::CAMPO_MONEDA] = $paisNombre;

                $numeroCompraElemento = $element->children(2);
                $numeroVentaElemento = $element->children(3);
                // las filas de cabecera tienen un solo children td, entonces continuar
                if ($numeroCompraElemento === null || $numeroVentaElemento === null)
                {
                    continue;
                }

                // extraer los montos en guaranies de compra
                preg_match($patronNumero, $numeroCompraElemento->innertext, $coincidencias, PREG_OFFSET_CAPTURE);

                // si hay entonces quitar el nombre del pais
                if (count($coincidencias) > 0)
                {
                    // obtener el nombre del pais
                    foreach ($coincidencias as $encontrados)
                    {
                        $valorCompra = $encontrados[0];
                        $valorCompra = str_replace(";", "", $valorCompra);
                        $valorCompra = str_replace("<", "", $valorCompra);
                        $valorCompra = trim($valorCompra);
                        // echo '<pre>'; var_dump($valorCompra); echo '</pre>';
                        break; // solo se obtiene el primer valor de coincidencia
                    }
                }

                $cotizacion[Api_Model_Entidad::CAMPO_COMPRA] = $valorCompra;

                // extraer los montos en guaranies de venta
                preg_match($patronNumero, $numeroVentaElemento->innertext, $coincidencias, PREG_OFFSET_CAPTURE);

                // si hay entonces quitar el nombre del pais
                if (count($coincidencias) > 0)
                {
                    // obtener el nombre del pais
                    foreach ($coincidencias as $encontrados)
                    {
                        $valorVenta = $encontrados[0];
                        $valorVenta = str_replace(";", "", $valorVenta);
                        $valorVenta = str_replace("<", "", $valorVenta);
                        $valorVenta = trim($valorVenta);
                        // echo '<pre>'; var_dump($valorVenta); echo '</pre>';
                        break; // solo se obtiene el primer valor de coincidencia
                    }
                }

                $cotizacion[Api_Model_Entidad::CAMPO_VENTA] = $valorVenta;

                $cotizaciones[] = $cotizacion;
            }
        }

        return $cotizaciones;
    }
}
?>