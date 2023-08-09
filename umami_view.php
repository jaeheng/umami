<?php
!defined('EMLOG_ROOT') && exit('access denied!');

if (!class_exists('Umami', false)) {
    include __DIR__ . '/umami_class.php';
}
// 加载静态资源
Umami::getInstance()->loadStaticPublic();
$umami_domain = Umami::getInstance()->getDomain();
$token = Umami::getInstance()->getToken();
$userInfo = Umami::getInstance()->getUserInfo();
$username = $userInfo['user']['username'];
?>
<div id="app" class="umami">
    <template v-if="umami_domain">
        <!--已填写实例地址-->
        <?php if($token):?>
            {{ userInfo.username }}已登录
        <?php else:?>
            <el-button @click="openDialog">登录</el-button>
        <?php endif;?>
        <el-button @click="openSettingDialog">设置</el-button>
    </template>


    <!--未填写实例地址-->
    <el-empty description="请先配置umami实例地址" v-else>
        <el-button @click="openSettingDialog" size="large" type="primary">配置umami实例地址</el-button>
    </el-empty>

    <el-dialog
        v-model="dialogVisible"
        title="登录umami"
        width="30%"
    >

        <el-form :model="form" label-position="top" :rules="rules" ref="form">
            <el-form-item label="登录账号" prop="username">
                <el-input type="text" v-model="form.username" placeholder="请输入登录账号" ></el-input>
            </el-form-item>
            <el-form-item label="登录密码" prop="password">
                <el-input type="password" v-model="form.password" placeholder="请输入登录密码" ></el-input>
            </el-form-item>
        </el-form>

        <template #footer>
          <span class="dialog-footer">
            <el-button @click="dialogVisible = false">取消</el-button>
            <el-button type="primary" @click="submit" :loading="submitting">
              登录
            </el-button>
          </span>
        </template>
    </el-dialog>

    <el-dialog
        v-model="settingDialogVisible"
        title="设置"
        width="50%"
    >

        <el-form :model="settingForm" label-position="top" :rules="settingRules" ref="settingForm">
            <el-form-item label="umami系统地址" prop="umami_domain">
                <el-input type="text" v-model="settingForm.umami_domain" placeholder="请输入umami系统地址" ></el-input>
            </el-form-item>
        </el-form>

        <template #footer>
          <span class="dialog-footer">
            <el-button @click="settingDialogVisible = false">取消</el-button>
            <el-button type="primary" @click="saveSetting" :loading="submitting">
              保存
            </el-button>
          </span>
        </template>
    </el-dialog>
</div>


<script>
    const { createApp, ref } = Vue

    const app = createApp({
        data () {
            return {
                umami_domain: '<?= $umami_domain?>',
                dialogVisible: false,
                settingDialogVisible: false,
                form: {
                    username: '',
                    password: ''
                },
                rules: {
                    username: [
                        { required: true, message: '请输入登录账号', trigger: 'blur' }
                    ],
                    password: [
                        { required: true, message: '请输入登录密码', trigger: 'blur' }
                    ]
                },
                settingForm: {
                    umami_domain: '<?= $umami_domain?>'
                },
                settingRules: {
                    umami_domain: [
                        { required: true, message: '请输入umami系统地址: http(s)://test.com', trigger: 'blur' }
                    ]
                },
                userInfo: {
                    username: '<?= $username?>'
                },
                userList: [],
                data: {},
                submitting: false,
                loading: false
            }
        },
        computed: {
            height () {
                return (window.innerHeight - 290) + 'px'
            },
        },
        methods: {
            openDialog() {
                this.dialogVisible = true
            },
            openSettingDialog() {
                this.settingDialogVisible = true
            },
            submit () {
                const that = this
                this.$refs.form.validate(valid => {
                    if (valid) {
                        const form = this.form
                        this.submitting = true
                        $.ajax({
                            type: 'post',
                            url: '<?= BLOG_URL . 'admin/plugin.php?plugin=umami';?>',
                            data: {
                                route: 'login',
                                username: form.username,
                                password: form.password
                            },
                            dataType: 'json',
                            success: function (resp) {
                                that.$message.success(resp.msg)
                                that.submitting = false
                                that.dialogVisible = false
                                that.fetchData()
                            },
                            error: function (err) {
                                that.$message.error(err)
                                that.submitting = false
                            }
                        })
                    }
                })

            },
            saveSetting () {
                const that = this
                this.$refs.settingForm.validate(valid => {
                    if (valid) {
                        const umami_domain = this.settingForm.umami_domain
                        this.submitting = true
                        $.ajax({
                            type: 'post',
                            url: '<?= BLOG_URL . 'admin/plugin.php?plugin=umami';?>',
                            data: {
                                route: 'save_umami_domain',
                                umami_domain: umami_domain
                            },
                            dataType: 'json',
                            success: function () {
                                that.$message.success('修改成功，请重新登录')
                                that.submitting = false
                                that.settingDialogVisible = false
                                that.umami_domain = umami_domain
                            },
                            error: function (err) {
                                that.$message.error(err)
                                that.submitting = false
                            }
                        })
                    }
                })

            },
            logout () {
                $.ajax({
                    type: 'post',
                    url: '<?= BLOG_URL . 'admin/plugin.php?plugin=umami';?>',
                    data: {
                        route: 'logout'
                    },
                    dataType: 'json',
                    success: function (resp) {
                        window.location.reload()
                    },
                    error: function (err) {}
                })
            },
            fetchData () {
                const that = this
                this.loading = true
                $.ajax({
                    type: 'post',
                    url: '<?= BLOG_URL . 'admin/plugin_user.php?plugin=rss_tracker';?>',
                    data: {
                        route: 'fetchRSS'
                    },
                    dataType: 'json',
                    success: function (resp) {
                        const data = resp.data
                        that.list = data.list
                        that.total = data.total
                        that.loading = false
                    },
                    error: function () {
                        that.$message.error('获取数据失败')
                        that.loading = false
                    }
                })
            },
        },
        mounted () {
            this.fetchData()
        }
    })
    app.use(ElementPlus);
    app.mount('#app');

    // emlog相关
    setTimeout(hideActived, 3600);
    $("#menu_category_ext").addClass('active');
    $("#menu_ext").addClass('show');
    $("#umami").addClass('active');
</script>
