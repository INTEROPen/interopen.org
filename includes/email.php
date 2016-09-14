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
	document.getElementById("emaila").innerHTML = decodeEntities(str2+str3);
	document.getElementById("emaila").setAttribute("href", decodeEntities(str1+str2+str3));
-->
</script>