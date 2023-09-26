# CommentPush

> Typecho 插件——新评论微信通知 


## 支持的推送模式
本插件的推送采用了
[Server酱](https://sct.ftqq.com/)
**telegram bot API**

## 使用方法

[点此下载](https://github.com/heinu123/CommentPush/archive/master.zip)后解压 将解压后的目录名改为 `CommentPush`，然后上传到你的 Typecho目录/usr/plugins，并在 Typecho 后台开启插件

## server酱
 1. 到[Server酱](http://sct.ftqq.com/)里申请你的专属 `SCKEY`，并根据提示绑定你的微信
 2. 将你申请到的 `SCKEY` 填写到插件设置里，保存即可
## telegram bot API
 1. 到[BotFather](https://t.me/BotFather)创建你的bot 并获取bot的`TOKEN` 具体就不细讲了 翻译即可
 2. 将你的`TOKEN`填写到插件设置里
 3. 到[Rose](https://t.me/MissRose_bot)机器人中发送`/if` 机器人返回的`此群组的 ID 为：xxxx`就是你的`对话ID` (私聊内发送为用户ID 群组内发送为群组id)
 4. 将你的`对话ID`填写到插件设置里，保存即可
