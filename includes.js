 $(document).ready(function(){
    // JFCustomWidget.subscribe("ready", asdasd);
    // function asdasd(argument) {
    //     qweqwe(form_id);
    // }

    // function formData(array){
    //     var formdata = new FormData();
    //     array.forEach(function(element){
    //         formdata.append(Object.keys(element), element);
    //     })
    //     return formData;
    // }

    // cloudsArray = clouds.split(",");
    // cloudsArray.shift();
    // cloudsArray.forEach(function(e){
    //     if((clouds.toLowerCase()).indexOf("amazonwebservices") != -1){
    //         if(empty(getAwsKeys())){
    //             document.getElementById("upload").disable = true;
    //             document.getElementById("aws").value = getAwsKeys();
    //         }
    //         else{
    //             document.getElementById("text").innerHTML = "";
    //             document.getElementById("aws").value = null;
    //         }
    //     }
    // })

    JFCustomWidget.subscribe("ready", function (formId) {
        setFrameSize(100); //Making iframe's height 100
        const form_id = formId["formID"]; //Getting form id
        const filekey = generate_token(16); //Creating uniq key for files
        const qid = JFCustomWidget.getWidgetSetting("qid"); //Getting question id
        const clouds = JFCustomWidget.getWidgetSetting("clouds"); //Getting which services selected
        checkDB(form_id,qid,clouds); //Checking database, is any key there for this form
        var status = JSON.parse(document.getElementById("hidden").value); //Getting checkDB result
        dropboxAuth(status,clouds,form_id,qid); //If dropbox choosed and doesn't have a key opening auth screen
        driveAuth(status,clouds,form_id,qid); //If drive choosed and doesn't have a key opening auth screen
        $("#upload").change(function(e) { //Listening upload item for is any file selected to upload
            e.preventDefault(); //Do nothing let me work for you command
            startUpload("upload"); //Starts upload selected files
           $("#url").change(function(e){ //Listening url item for file url's
                returnSubmit(JSON.stringify(document.getElementById("url").value),true); //Giving feedback about field to formm
           })
            // var folder = document.getElementById("folder").value;
            // removeFiles(form_id,folder);                                   
        });
        JFCustomWidget.subscribe("submit", submitFunc);


		function startUpload(elementId){
            var input = document.getElementById(elementId);  
            var currentHeight = window.innerHeight;
            var totalItemHeights = (input.files.length) * 90;
            var height = currentHeight + totalItemHeights;
            setFrameSize(height);
            for(var x = 0;x < input.files.length; x++){
                var file = input.files[x];
                upload(form_id,qid,file,filekey,x);

            }
        }
        function checkDB(form_id,qid,clouds){
            var formdata = new FormData();
            formdata.append("formid", form_id);
            formdata.append("qid",qid);
            formdata.append("action", "select");
            formdata.append("clouds",clouds);
            document.getElementById("hidden").value = ajaxRequest("database.php",formdata,false);
        }

        function dropboxAuth(status,clouds){
            if((clouds.toLowerCase()).indexOf("dropbox") != -1){ 
                if(empty(status.Dropbox)){
                    document.getElementById("dropbox").value = false;
                    var client_id = "9pdfdpjr2pnqzmo";
                    var dbx = new Dropbox.Dropbox({clientId: client_id});
                    var authUrl = dbx.getAuthenticationUrl("https://toprak.jotform.pro/Adapter/oauth.html"); 
                    var AuthWindow = window.open(authUrl,"AuthWindow",'location=0,status=0,width=800,height=400');
                    $("#key").change(function(){
                        key = document.getElementById("key").value;
                        var formdata = new FormData();
                        formdata.append("key",key);
                        formdata.append("qid",qid);
                        formdata.append("action", "insert");
                        formdata.append("value","dropbox");
                        formdata.append("clouds","Dropbox");
                        formdata.append("formid", form_id);
                        ajaxRequest("database.php",formdata,false);
                        AuthWindow.close();
                    });
                }
            }
            else{
                document.getElementById("dropbox").value = true;                        
            } 
        }
        function driveAuth(status,clouds,form_id,qid){
            if((clouds.toLowerCase()).indexOf("drive") !=  -1){
                if(empty(status.Drive)){
                    document.getElementById("drive").value = false;
                    this.onload=function(){};handleClientLoad();
                    var key = "AIzaSyAbodyJck6jBlV4oV9A3-E6xdMvf3JsdDg";
                    var client = "424058548993-sbd0hd1dflne6507emj91gad1pf9bebc.apps.googleusercontent.com";
                    var discovery = ["https://www.googleapis.com/discovery/v1/apis/drive/v3/rest"];
                    var scopes = "https://www.googleapis.com/auth/drive";
                    function handleClientLoad() {
                        gapi.load("client:auth2",initClient);
                    }
                    function initClient() {
                        gapi.client.init({
                            apiKey: key,
                            clientId: client,
                            discoveryDocs: discovery,
                            scope: scopes
                        }).then(function (){
                            gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);
                            updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());
                            handleAuthClick();
                        });
                    }
                    function updateSigninStatus(isSignedIn) {
                        if (isSignedIn) {
                            // console.log("signed");
                        }
                        addEventListener
                        {
                            // console.log("error");
                        }
                    }
                    function handleAuthClick() {
                        // console.log("handleAuthClick");
                        gapi.auth2.getAuthInstance().signIn();
                        gapi.auth2.getAuthInstance().grantOfflineAccess().then(function(code){
                            key = JSON.stringify(code);
                            var formdata = new FormData();
                            formdata.append("qid",qid);
                            formdata.append("value","drive");
                            formdata.append("key",key);
                            formdata.append("formid", form_id);
                            formdata.append("clouds", "Drive");
                            formdata.append("action", "insert");
                            ajaxRequest("database.php",formdata,false);
                        })
                    }
                }
            }
        }
        function empty(input) {
            if(input == "" || input == 0 || input == "0" || input == null || input == false || input == undefined || input == "null" || input == "{}")  {
                return true;
            }
            else {
                return false;
            }
        }
        function upload(formid,qid,file,filekey,progressName){
            var folder = null;
            var formdata = new FormData();
            var id =  createDiv(generate_token(4),file.name,getFileExtension(file.name));
            formdata.append("qid",qid);
            formdata.append("file", file); 
            formdata.append("formid",formid);
            formdata.append("filekey",filekey);
            formdata.append("action","save");
            formdata.append("key",document.getElementById("aws").value);
            var progressBar = "progressBar-"+id;
            save(formdata,filekey,progressBar).done(function(result){
                if(empty(document.getElementById("folder").value)){
                    document.getElementById("folder").value = result; 
                    folder = JSON.parse(document.getElementById("folder").value);
                    document.getElementById("folder").value = folder["folder"];
                }
                let qid = JFCustomWidget.getWidgetSetting("qid");
                folder = document.getElementById("folder").value;
                var callback = null;
                var folderKey = document.getElementById("folderKey").value;
                callBack = JSON.parse(sendJob(qid,file.name,formid,folder,folderKey, getAwsKeys()));
                if(empty(document.getElementById('folderKey').value)){
                    let folderId = JSON.parse(callBack.Drive);
                    folderId = folderId.Folder;
                    document.getElementById("folderKey").value = folderId;
                }
                console.log(callBack);
                dropbox = JSON.parse(callBack.Dropbox);
                dropboxUrl = dropbox.Url;
                dropboxError = dropbox.Error;
                dropboxRemove = dropbox.Remove;
                console.log("----DBX----\n" + dropboxError +"\n"+ dropboxUrl +"\n"+ dropboxRemove+"\n----DBX----");
                drive = JSON.parse(callBack.Drive);
                driveUrl = drive.Url;
                driveError = drive.Error;
                driveRemove = drive.Remove;
                console.log("----GDR----\n"+driveError +"\n"+ driveUrl +"\n"+ driveRemove+"\n----GDR----");
                amazon = JSON.parse(callBack.AmazonWebServices);
                amazonUrl = amazon.Url;
                amazonError = amazon.Error;
                amazonRemove = amazon.Remove;
                console.log("----AWS----\n"+amazonError +"\n"+ amazonUrl +"\n"+ amazonRemove+"\n----AWS----");
                
                document.getElementById("url").value = "Dropbox:" + dropboxUrl +"<br>Drive:" + driveUrl + "<br>Amazon Web Services:" + amazonUrl;
                var remove = JSON.parse(JSON.stringify({"Dropbox": dropboxRemove,"Drive": driveRemove,"Amazon": amazonRemove})); 
                document.getElementById("remove-"+id).value = JSON.stringify({"formid": formid,"qid": qid,"Remove":remove});
               
                $("#url").trigger("change");
            }).fail(function(){
                console.log("An Error Occured");
            });
            return true;
        }
        function save(formdata,filekey,progressId){
            return jQuery.ajax({
                    type: "POST",
                    url: "file.php",
                    enctype: "multipart/form-data",
                    data: formdata,
                    cache: false,
                    // async: false,
                    contentType: false,
                    processData: false,
                    xhr: function () {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {   
                            xhr.upload.addEventListener("progress", function (e) {
                                var percent = 0;
                                var position = e.loaded || e.position;
                                var total = e.total;
                                if (e.lengthComputable) {
                                    percent = Math.ceil(position / total * 100);
                                }
                                $("#"+progressId).attr("value", percent);
                            }, true);
                        }
                        return xhr;
                    },
                    success: function (sccss) {
                    }
                });
        }   
        function ajaxRequest(url,formdata,async){
            var result = null;
            jQuery.ajax({
                type: "POST",
                url: url,
                enctype: "multipart/form-data",
                data: formdata,
                async: async,
                cache: false,
                contentType: false,
                processData: false,
                success: function(sccss){
                    result = sccss;
                }
            })
            return result;
        }
        function sendJob(qid,file,formid,folder,folderKey,key){
            var formdata = new FormData();
            formdata.append("key",key);
            formdata.append("qid",qid);
            formdata.append("file",file);
            formdata.append("folder",folder);
            formdata.append("formid",formid);
            formdata.append("action","upload");
            formdata.append("folderKey",folderKey);
            var response = ajaxRequest("file.php",formdata,false);
            return response;
        }
        function getAwsKeys(){
            var access = JFCustomWidget.getWidgetSetting("awsaccess");
            var secret = JFCustomWidget.getWidgetSetting("awssecret");
            var bucket = JFCustomWidget.getWidgetSetting("awsbucket");
            var region = JFCustomWidget.getWidgetSetting("awsregion");
            var key =JSON.stringify({access: access,secret: secret, bucket: bucket,region: region});
            return key;
        }
        function urlReplace(url){
            do{
                old = url;
                url = url.replace("\\","");
                url = url.replace("{","");
                url = url.replace("}","");
                url = url.replace(",","<br>");
                url = url.replace(" ","%20"); //Space = %20
            }
            while(old != url);
            return url;
        }
        function generate_token(length){
            var a = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890".split("");
            var b = [];  
            for (var i=0; i<length; i++) {
                var j = (Math.random() * (a.length-1)).toFixed(0);
                b[i] = a[j];
            }
            return b.join("");
        }
        function returnSubmit(value,valid){
            var result = {};
            result.value = value;
            result.valid = valid;
            JFCustomWidget.sendSubmit(result);
        }
        function createDiv(id,file = null,fileExtension = null){ //Creating div

            var uploadItem = document.createElement("div");
            var imageSection = document.createElement("div");
            var img = document.createElement("img");
            var fileType = document.createElement("div");
            var progressSection = document.createElement("div");
            var progressFile = document.createElement("div");
            var progressBar = document.createElement("progress");
            var remove = document.createElement("button");

            uploadItem.id = "uploadItem-"+id;
            uploadItem.classList.add("uploadItem");
            imageSection.id = "imageSection-"+id;
            imageSection.classList.add("imageSection");
            img.setAttribute("alt","img");
            img.setAttribute("src","https://i.hizliresim.com/dOzJkZ.png");
            fileType.id = "fileType-"+id;
            fileType.innerHTML = fileExtension.toUpperCase();
            progressSection.id = "progressSection-"+id;
            progressSection.classList.add("progressSection");
            progressFile.id = "progressFile-"+id;
            progressFile.innerHTML = file;
            progressBar.id = "progressBar-"+id;
            remove.classList.add("remove");
            remove.id = "remove-"+id;
            remove.innerHTML = '✕';

            var measurement = document.createElement("span");
            document.getElementById("xup").appendChild(measurement);
            measurement.id = "measurement-"+id;
            measurement.classList.add("measurement");
            document.getElementById(measurement.id).innerHTML = file;
            if(document.getElementById(measurement.id).offsetWidth > 200){
                progressFile.classList.add("progressFile-Long");
                var indentValue = document.getElementById(measurement.id).offsetWidth-190;
                var css = '#progressFile-'+id+':hover{ text-indent:-'+indentValue+'px; }';
                head = document.head || document.getElementsByTagName('head')[0],
                style = document.createElement('style');
                style.type = 'text/css';
                if (style.styleSheet){
                  style.styleSheet.cssText = css;
                } else {
                  style.appendChild(document.createTextNode(css));
                }

                head.appendChild(style);
            }
            measurement.remove();
            
            document.getElementById("xup").appendChild(uploadItem);
            document.getElementById(uploadItem.id).appendChild(imageSection);
            document.getElementById(imageSection.id).appendChild(img);
            document.getElementById(imageSection.id).appendChild(fileType);
            document.getElementById(uploadItem.id).appendChild(progressSection);
            document.getElementById(progressSection.id).appendChild(progressFile);
            document.getElementById(progressSection.id).appendChild(progressBar);
            document.getElementById(uploadItem.id).appendChild(remove);

           $("#"+remove.id).click(function(e){ //If user click X button this is removing file from clouds and destroying div
                e.preventDefault();
                var params = JSON.parse(document.getElementById(remove.id).value);
                var path = params.Remove;
                path = JSON.stringify(path);
                console.log(path);
                var formdata = new FormData();
                formdata.append("action","deleteFile");
                formdata.append("formid", params.formid);
                formdata.append("qid", params.qid);
                formdata.append("remove", path);
                formdata.append("aws",getAwsKeys());
                ajaxRequest("file.php",formdata,true);
                var elements = document.getElementById("xup").children;
                var totalItemHeights = (elements.length - 1) - 80;
                if(totalItemHeights <= 500){
                    var height = totalItemHeights;
                    setFrameSize(height);
                }
                $("#"+uploadItem.id).remove();
           });
            return id;
        }
        function getFileExtension(filename){
            return filename.split('.').pop();
        }
        function setFrameSize(height,width = 500){
            if(height < 530){
                var size ={}; 
                size.width = width; 
                size.height =  height; 
                JFCustomWidget.requestFrameResize(size); 
            }
            else{
                var size ={}; 
                size.width = width; 
                size.height =  530; 
                JFCustomWidget.requestFrameResize(size);
            }
            return true;
        }
        function submitFunc(){
            fieldCheck();
        }  
        function fieldCheck(){ //If field has no file user can't skip question (is required) and submit form
            var children = empty(document.getElementById("xup")) ? 0 : document.getElementById("xup").children;
            for(let i = 0; i <= children.length - 1;  i++){
                if(children[i].id.indexOf("uploadItem") == -1){
                    if(i == (children.length-1)){
                        console.log(children[i].id);
                        document.getElementById("url").value = null;
                        returnSubmit(null,false);
                    }
                }
            }
            var folder = document.getElementById("folder").value;
            if(!empty(document.getElementById("url").value)){
                // removeFiles(form_id,folder);                                   
                returnSubmit(JSON.stringify(document.getElementById("url").value),true);
            }
            else{
                returnSubmit(null,false);
            }
        }
    });
})