/** 
*   @author Toprak Koc
*    @constructor
*/
 $(document).ready(function(){
    JFCustomWidget.subscribe("ready", function (formId) {
        setFrameSize(100);
        const form_id = formId["formID"]; 
        const filekey = generate_token(16); 
        const qid = JFCustomWidget.getWidgetSetting("qid");
        const clouds = JFCustomWidget.getWidgetSetting("clouds");
        checkDB(form_id,qid,clouds); 
        var status = JSON.parse(document.getElementById("hidden").value);
        dropboxAuth(status,clouds,form_id,qid); 
        driveAuth(status,clouds,form_id,qid); 
        // mahmutla takılıyoruz
        $("#upload").change(function(e) { 
            e.preventDefault(); 
            startUpload("upload");
           $("#url").change(function(e){ 
                returnSubmit(JSON.stringify(document.getElementById("url").value),true);
           });
            // var folder = document.getElementById("folder").value;
            // removeFiles(form_id,folder);                                   
        });
        JFCustomWidget.subscribe("submit", submitFunc);

        /**
        *   Starts Upload Jobs 
        *   @param {String} elementId - Id of select button
        */
        function startUpload(elementId){
            mahmut();
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
        /**
        *   Checks Database for Any Saved Keys
        *   @param {String} form_id - Id of form
        *   @param {String} qid - Question Id
        *   @param {String} clouds - Selected Cloud Services
        */
        function checkDB(form_id,qid,clouds){
            var formdata = new FormData();
            formdata.append("formid", form_id);
            formdata.append("qid",qid);
            formdata.append("action", "select");
            formdata.append("clouds",clouds);
            document.getElementById("hidden").value = ajaxRequest("database.php",formdata,false);
        }
        /**
        *   Authorizing Dropbox and Saving Keys
        *   @param {Object} status - checkDB Result
        *   @param {String} clouds - Selected Cloud Services
        */
        function dropboxAuth(status,clouds){
            if((clouds.toLowerCase()).indexOf("dropbox") != -1){ 
                if(empty(status.Dropbox)){
                    // document.getElementById("dropbox").value = false;
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
        /**
        *   Authorizing Drive and Saving Keys
        *   @param {Object} status - CheckDB Result
        *   @param {String} clouds - Selected Cloud Services
        *   @param {String} form_id - Id of Form
        *   @param {String} qid - Question Id
        */
        function driveAuth(status,clouds,form_id,qid){
            if((clouds.toLowerCase()).indexOf("drive") !=  -1){
                if(empty(status.Drive)){
                    // document.getElementById("drive").value = false;
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
                        });
                    }
                }
            }
        }
        function empty(input) {
            return input == "" || input == 0 || input == "0" || input == null || input == false || input == undefined || input == "null" || input == "{}" ? true : false;
        }
        /**
        *   Saving selected files to tmp folder then starts uploading to cloud services
        *   @param {String} formid - Id of Form
        *   @param {String} qid - Question Id
        *   @param {File} file - Selected File
        *   @param {String} filekey - File key is like a form id its uniq and for multiple uploads
        *   @param {String} progressName - Progressbar's Name
        */
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
            });
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
        /**
        *   Creating UI elements for selected file
        *   @param {String} id - Uniq id for elements
        *   @param {String} file - File name for remove func 
        *   @param {String} fileExtension - File Extension for UI Elements
        */
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
                head = document.head || document.getElementsByTagName('head')[0];
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

           $("#"+remove.id).click(function(e){ //If user click remove (X) button this is removing file from clouds and destroying div
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
                // var oldHeight = empty(document.getElementById("height").value) ? 0 : document.getElementById("height").value;
                // var newHeight = oldHeight - 90;
                // if((newHeight < 530) && (newHeight > 100)){
                //     setFrameSize(newHeight);
                // }
                var elements = empty(document.getElementById("xup").children) ? 0 : document.getElementById("xup").children.length - 3;
                var currentHeight = elements * 90;
                console.log(elements + "\n" + currentHeight);
                var newHeight = currentHeight-80>530 ? 530 : currentHeight - 90;
                setFrameSize(currentHeight);
                $("#"+uploadItem.id).remove();
           });
            return id;
        }
        function getFileExtension(filename){
            return filename.split('.').pop();
        }
        function setFrameSize(height,width = 500){
            var size ={}; 
            size.width = width>500 ? 500 : width; 
            size.height =  height<530 ? height>100 ? height : 100 : 530;   //Used if statement because height max value have to be 530
            JFCustomWidget.requestFrameResize(size); 
        }
        function submitFunc(){
            fieldCheck();
        }  
        /**
            If field has no file user can't skip the question (if is required) and submit the form
        */
        function fieldCheck(){
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
                var folder = document.getElementById("folder").value;
                removeFiles(form_id,folder);                                         
                returnSubmit(JSON.stringify(document.getElementById("url").value),true);
            }
            else{
                returnSubmit(null,false);
            }
        }
        window.onerror = function(err,url,line) {
            document.getElementById("add").disabled = true;
            document.getElementById("upload").disabled = true;
            setFrameSize(300);
            var error = document.createElement("div");
            error.innerHTML = err; //+ "<br>" + url + "<br>" + line;
            document.getElementById("xup").appendChild(error);
            head = document.head || document.getElementsByTagName('head')[0];
            var css = ".addFile{ visibility: hidden}";
            style = document.createElement("style");
            style.type = "text/css";
            if (style.styleSheet){
                style.styleSheet.cssText = css;
            } else {
                style.appendChild(document.createTextNode(css));
            }
            head.appendChild(style);
        };
    });//JFCustomWdiget.Subscribe
});//Document.Ready