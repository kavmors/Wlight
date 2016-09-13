# **各版本更新内容** #

## **3.1** ##

更新内容：

> - 类库新增common命名空间，包含ConfigLoader、Statis(3.0版本中位于statis空间)
> - 修改HttpClient，执行超时时间可设置1s以下
> - 修复customservice下Message类库的BUG
> - 数据库表变化：合并wlight\_statis\_tag和wlight\_statis\_tag\_map(删除wlight\_statis\_tag\_map)

## **3.0** ##

更新内容：

> - 优化Log输出
> - 新增留言表
> - 修改部分配置名与数据库表名

数据库表变化：

- wlight\_tag -> wlight\_statis\_tag
- wlight\_tag\_map -> wlight\_statis\_tag\_map
- wlight\_cache -> wlight\_cache\_msg
- wlight\_statis\_message(新增)
- wlight\_cache\_retry(新增)
- wlight\_cache\_oauth(新增)

配置常量变化：

- RECORD\_LIVE -> STATIS\_LIVE
- WLIGHT\_URL(新增)
- APP\_URL(新增)
- RES\_URL(新增)
- DEFAULT\_PERMISSION(新增)
- LOCK\_CACHE(删除)
- LOCK\_ACCESS_TOKEN(删除)
- LOCK\_JSAPI_TICKET(删除)

**3.0起不再支持Memcache，全部改用数据库缓存，如有需要请自行开发。**
