

function tex_ajax(url,id){
	//url like '/ajax&do=something'
	//NOT WWW
	var xmlHttp;    
	xmlHttp=tex_GetXmlHttpObject();
	HtmlID=id;

	xmlHttp.onreadystatechange= function (){ 
		/*
	0	The request is not initialized
	1	The request has been set up
	2	The request has been sent
	3	The request is in process
	4	The request is complete
		 */

		if (xmlHttp.readyState==4)
		{
			document.getElementById(id).innerHTML=xmlHttp.responseText;		
		}
	}
	
	xmlHttp.open("GET",url,true);
	xmlHttp.send(null);
}




//creates a XmlHttpObject...
function tex_GetXmlHttpObject()
{
var xmlHttp=null;
try
  {
  // Firefox, Opera 8.0+, Safari
  xmlHttp=new XMLHttpRequest();
  }
catch (e)
  {
  // Internet Explorer
  try
    {
    xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
    }
  catch (e)
    {
    xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
  }
  
  if (xmlHttp==null)
  {
  alert ("Your browser does not support AJAX!");
  return;
  }
  
  
return xmlHttp;
}