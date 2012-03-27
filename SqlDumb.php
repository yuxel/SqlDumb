<?php
/*-------------------------------------------
MySQL veritabanı yedekleme kodu
Aşağıdaki değişkenleri ayarlayın
Kullanımdan doğacak hatalardan yazar sorumlu tutulamaz
* MySQL 3.23.20 veya üzeri bir veritabanı sunucunuz olmalı

Osman Yüksel  11.02.2005
--------------------------------------------*/
$vthost="localhost";    //veritabanı host
$vtkullanici="root";    //veritabanı kullanıcı adı
$vtsifre="12345678";        //veritabanı şifresi
$vtadi="wwwp";        //yedeklenecek veritabanı adı
$ara="";        /*--eğer sadece belli bir önek veya belli bir tablonun
            //yedeklenmesini istiyorsanız bunu kullanabilirsiniz
            ara="aranacak" gibi bir değer belirlerseniz sadece
            içersinde "aranacak" geçen tablolar yedeklenecektir*/
$dosya_adi="yedek.sql";    //yedeklerin yazılacağı dosya

/*------------------------------------*/

if(!is_writeable(".")) echo "Yazma izniniz bulunmuyor";
else
{
  $baglan=mysql_connect($vthost,$vtkullanici,$vtsifre);
  $sec=mysql_select_db($vtadi,$baglan);
  if(!$sec) { echo "Veritabanına bağlanılamadı"; }
  else
    {
      $tablolar=mysql_list_tables($vtadi);  //tablo listesi
      $tablosayisi=mysql_num_rows($tablolar); //veritabanındaki tablo sayısını bul
      for ($a=0;$a<$tablosayisi;$a++)
    {  // her tablo için işlem yap
      $row=mysql_fetch_row($tablolar);
      if(preg_match("/$ara/", $row[0]))
        {  //sadece belirli ön ekle başlayanları al
          $tablename=$row[0];
          $crtable=mysql_query("show create table $tablename");
          //her tablo için show create table komutu ile iste
          //bu özellik MySQL 3.23.20 den itibaren var
          $tmpres = mysql_fetch_row($crtable);
          $cikti .= $tmpres[1].";";  //create table'ların sonuna ; koy
          $cikti .= "\n\n\n";  //create table komutlarından sonra 3 satır boşluk ver
          $alanlar=mysql_query("select * from `$tablename`");
          //her field için insert into komutlarını hazırla
          $alansayisi=mysql_num_fields($alanlar);  //alan sayısı
          $nr=mysql_num_rows($alanlar);  //row sayısı
          for ($c=0;$c<$nr;$c++)
        { //her row için
          $cikti .= "insert into `$tablename` values (";
          $row=mysql_fetch_row($alanlar);  //alan adlarını ' karakterleriyle yazdır
          for ($d=0;$d<$alansayisi;$d++)
            {
              $data=strval($row[$d]);
              $cikti .="'".addslashes($data)."'";  // ' i kontrol için
              if ($d<($alansayisi-1))
            {
              $cikti .=", ";  //her alan için araya virgül koy
            } #if
            } #for
                $cikti .=");\n"; // parantezi kapat
        } #for
        } #if ->ön ekleri al
    } #if  ->her tablo için


      $yaz=fopen($dosya_adi, "w");  //$cikti'yi $dosya_adi'na yazdir
      fwrite($yaz,$cikti);
      fclose($yaz);
      echo "Veritabanı yedeği $dosya_adi dosyasına kaydedildi";

    } #else  -> veritabanı bağlantı kontrolü

    mysql_close($baglan);
} #else  yazma kontrolü
