OC.L10N.register(
    "integration_zulip",
    {
    "Zulip messages" : "Zulip iletileri",
    "%s in #%s > %s at %s" : "%s #%s > %s içinde %s zamanda",
    "%s in %s at %s" : "%s, %s içinde %s zamanında",
    "Bad HTTP method" : "HTTP yöntemi hatalı",
    "Bad credentials" : "Kimlik doğrulama bilgileri hatalı",
    "Connected accounts" : "Bağlı hesaplar",
    "Zulip Integration" : "Zulip bütünleştirmesi",
    "Integration of Zulip" : "Zulip bütünleştirmesi",
    "This integration allows you to send Nextcloud files to your Zulip chat instance as uploaded files, public shared links, or internal shared links.\n\n## 🔧 Configuration\n\n### User settings\n\nAccount configuration happens in the \"Connected accounts\" user settings section.\nIt requires you to specify the URL of your Zulip instance, as well as the email address and API key associated with your Zulip account in that instance.\nThese values can be found in and copied from your Zulip account's `zuliprc` file located in **Personal settings > Account & privacy > API key**.\n\nIf those settings are not configured, a link to the \"Connected accounts\" user settings page will be displayed when attempting to send a file to a Zulip user/topic.\nThe context menu to send a file can be accessed by right clicking on the file/folder to be shared or selecting them and clicking on the \"Actions\" button." : "Bu bütünleştirme ile, Nextcloud dosyalarını Zulip sohbet kopyanıza yüklenen dosyalar, herkese açık paylaşımlı bağlantılar veya içeride paylaşılan bağlantılar olarak gönderebilirsiniz.\n\n## 🔧 Yapılandırma\n\n### Kullanıcı ayarları\n\nHesap yapılandırması \"Bağlı hesaplar\" kullanıcı ayarları bölümünde gerçekleşir.\nZulip kopyanızın adresini, o kopyadaki Zulip hesabınızın e-posta adresini ve API anahtarını belirtmeniz gerekir.\nBu değerler, **Kişisel ayarlar > Hesap ve gizlilik > API anahtarı** konumunda bulunan Zulip hesabınızdaki `zuliprc` dosyasında bulunabilir ve buradan kopyalanabilir.\n\nBu ayarlar yapılandırılmamışsa, bir Zulip kullanıcısına/konusuna dosya göndermeye çalışırken \"Bağlı hesaplar\" kullanıcı ayarları sayfasına bir bağlantı görüntülenir.\nBir dosya göndermek için sağ tık menüsüne, paylaşılacak dosya/klasöre sağ tıklanarak veya bunları seçip \"İşlemler\" düğmesine tıklanarak erişilebilir.",
    "Zulip options saved" : "Zulip seçenekleri kaydedildi",
    "Failed to save Zulip options" : "Zulip seçenekleri kaydedilemedi",
    "Zulip integration" : "Zulip bütünleştirmesi",
    "You can generate and access your Zulip API key from Personal settings -> Account & privacy -> API key." : "Zulip API anahtarınızı Kişisel ayarlar -> Hesap ve gizlilik -> API anahtarı bölümünden oluşturabilir ve öğrenebilirsiniz.",
    "Then copy the values in the provided zuliprc file into the fields below." : "Ardından aldığınız zuliprc dosyasındaki değerleri aşağıdaki alanlara kopyalayın.",
    "Zulip instance address" : "Zulip kopyası adresi",
    "Zulip account email" : "Zulip hesabının e-posta adresi",
    "Zulip API key" : "Zulip API anahtarı",
    "Add file action to send files to Zulip" : "Dosyaları Zulip üzerine yollayan bir dosya işlemi ekleyin",
    "Enable searching for messages" : "İletilerde arama yapılabilsin",
    "password" : "parola",
    "Message to send with the files" : "Dosyalar ile gönderilecek ileti",
    "View only" : "Yalnızca görüntüleme",
    "Edit" : "Düzenle",
    "Failed to load Zulip channels" : "Zulip kanalları yüklenemedi",
    "Failed to load Zulip topics" : "Zulip konuları yüklenemedi",
    "Files" : "Dosyalar",
    "Remove file from list" : "Listeden kaldır",
    "Conversation" : "Görüşme",
    "Select a channel or user" : "Bir kanal ya da kullanıcı seçin",
    "Topic" : "Konu",
    "Select a topic" : "Bir konu seçin",
    "Type" : "Tür",
    "Set expiration date" : "Geçerlilik sonu tarihini ayarla",
    "Expires on" : "Geçerlilik sonu",
    "Set link password" : "Bağlantı parolasını ayarla",
    "Comment" : "Yorum",
    "Directories will be skipped, they can only be sent as links." : "Klasörler atlanacak. Klasörler yalnızca bağlantılar olarak gönderilebilir.",
    "Cancel" : "İptal",
    "_Send file to Zulip_::_Send files to Zulip_" : ["Dosyayı Zulip üzerine gönder","Dosyaları Zulip üzerine gönder"],
    "_Send link to Zulip_::_Send links to Zulip_" : ["Bağlantıyı Zulip üzerine gönder","Bağlantıları Zulip üzerine gönder"],
    "_Send file_::_Send files_" : ["Dosya gönder","Dosyaları gönder"],
    "_Send link_::_Send links_" : ["Bağlantı gönder","Bağlantıları gönder"],
    "Send files to Zulip" : "Dosyaları Zulip üzerine gönder",
    "Failed to send {name} to {channelName} on Zulip" : "{name} Zulip üzerindeki {channelName} kanalına gönderilemedi",
    "_A link to {fileName} was sent to {channelName}_::_All of the {number} links were sent to {channelName}_" : ["Bir {fileName} bağlantısı {channelName} kanalına gönderildi","{number} bağlantının tümü {channelName} kanalına gönderildi"],
    "Failed to send links to Zulip" : "Bağlantılar Zulip üzerine gönderilemedi",
    "_Failed to send the internal link to {channelName}_::_Failed to send internal links to {channelName}_" : ["İç bağlantı {channelName} kanalına gönderilemedi","İç bağlantılar {channelName} kanalına gönderilemedi"],
    "_{fileName} was successfully sent to {channelName}_::_All of the {number} files were sent to {channelName}_" : ["{fileName} dosyası, {channelName} kanalına gönderildi","{number} dosyanın tümü {channelName} kanalına gönderildi"],
    "You need to connect a Zulip app before using the Zulip integration." : "Zulip bütünleştirmesini kullanmadan önce bir Zulip uygulaması ile bağlantı kurmalısınız.",
    "Do you want to go to your \"Connect accounts\" personal settings?" : "Kişisel ayarlarınızdaki \"Hesapları ilişkilendir\" bölümüne gitmek ister misiniz?",
    "Connect to Zulip" : "Zulip bağlantı kur",
    "Go to settings" : "Ayarlara git",
    "Upload files" : "Dosyaları yükle",
    "Public links" : "Herkese açık bağlantılar",
    "Internal links (Only works for users with access to the files)" : "İç bağlantılar (yalnızca bu dosyalara erişebilen kullanıcılar için geçerlidir)"
},
"nplurals=2; plural=(n > 1);");
