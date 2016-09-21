<script type="text/javascript">
<!--
    function decodeEntities(s)
	{
		var str, temp= document.createElement('p');
		temp.innerHTML= s;
		str= temp.textContent || temp.innerText;
		temp=null;
		return str;
	}

	str1="mailto:";
	str2="&#x73;&#x75;&#x70;&#x70;&#x6C;&#x69;&#x65;&#x72;&#x67;&#x72;&#x6F;&#x75;";
	str3="&#x70;&#x40;&#x69;&#x6E;&#x74;&#x65;&#x72;&#x6F;&#x70;&#x65;&#x6E;&#x2E;&#x6F;&#x72;&#x67;";
	
    var elements = document.getElementsByClassName("emaila");
    for (var i = 0; i < elements.length; i++)
    {
      elements[i].innerHTML = decodeEntities(str2+str3);
	  elements[i].setAttribute("href", decodeEntities(str1+str2+str3));        
    }
  
	str4="&#x6D;&#x65;&#x6D;&#x62;&#x65;&#x72;&#x73;&#x68;&#x69;&#x70;&#x40;&#x69;&#x6E;";
	str5="&#x74;&#x65;&#x72;&#x6F;&#x70;&#x65;&#x6E;&#x2E;&#x6F;&#x72;&#x67;";
  
    var elements2 = document.getElementsByClassName("emailb");
    for (var i = 0; i < elements2.length; i++)
    {
      elements2[i].innerHTML = decodeEntities(str4+str5);
	  elements2[i].setAttribute("href", decodeEntities(str1+str4+str5));        
    }
-->
</script>