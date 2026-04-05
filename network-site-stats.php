<?php
/**
 * Plugin Name: Network Site Stats
 * Description: Plugin quản lý tập trung - Thống kê bài viết của các trang con trong mạng lưới.
 * Version: 1.0
 * Author: Nguyễn Trọng An
 * Network: true
 */

// Ngăn chặn truy cập trực tiếp vào file
if (!defined('ABSPATH')) {
    exit;
}

class Network_Site_Stats_Plugin {

    public function __construct() {
        // Hook để thêm menu vào Network Admin Dashboard
        add_action('network_admin_menu', array($this, 'nss_add_menu'));
    }

    // 1. Tạo Menu trong trang Quản trị mạng
    public function nss_add_menu() {
        add_menu_page(
            'Thống kê mạng lưới',    // Tiêu đề trang
            'Site Stats',           // Tên menu hiển thị
            'manage_network',       // Quyền hạn (chỉ Super Admin)
            'network-site-stats',   // Slug của menu
            array($this, 'nss_render_page'), // Hàm hiển thị giao diện
            'dashicons-chart-area', // Icon biểu đồ
            30                      // Vị trí menu
        );
    }

    // 2. Hàm hiển thị giao diện bảng thống kê
    public function nss_render_page() {
        // Lấy danh sách tất cả các site trong mạng lưới
        $sites = get_sites();

        echo '<div class="wrap">';
        echo '<h1><span class="dashicons dashicons-chart-bar"></span> Báo cáo thống kê toàn mạng lưới</h1>';
        echo '<p>Dưới đây là dữ liệu tổng hợp từ các Site con (Site A, Site B...)</p>';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Tên Website (Blog Name)</th>
                    <th>Số lượng bài viết</th>
                    <th>Ngày đăng bài mới nhất</th>
                </tr>
              </thead>
              <tbody>';

        if (!empty($sites)) {
            foreach ($sites as $site) {
                $blog_id = $site->blog_id;

                // CHUYỂN NGỮ CẢNH sang site con để lấy dữ liệu đúng database
                switch_to_blog($blog_id);

                $site_name = get_bloginfo('name');
                $post_count = wp_count_posts()->publish; // Chỉ đếm bài đã xuất bản
                
                // Lấy ngày của bài viết mới nhất
                $recent_posts = get_posts(array(
                    'numberposts' => 1,
                    'post_status' => 'publish'
                ));
                $last_updated = !empty($recent_posts) ? $recent_posts[0]->post_date : 'Chưa có bài viết';

                echo "<tr>
                        <td><strong>#{$blog_id}</strong></td>
                        <td>{$site_name}</td>
                        <td><span class='badge' style='background:#0073aa; color:#fff; padding:2px 8px; border-radius:10px;'>{$post_count}</span></td>
                        <td>{$last_updated}</td>
                      </tr>";

                // KHÔI PHỤC NGỮ CẢNH ban đầu (Tránh lỗi hệ thống)
                restore_current_blog();
            }
        } else {
            echo '<tr><td colspan="4">Không tìm thấy trang web con nào.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
    }
}

// Khởi tạo class
new Network_Site_Stats_Plugin();