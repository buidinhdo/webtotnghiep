<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('is_admin', true)->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin GameStation',
                'email' => 'admin@gamestation.test',
                'password' => bcrypt('password'),
                'is_admin' => true
            ]);
        }

        Article::firstOrCreate(
            ['slug' => 'huong-dan-chon-game-ps5'],
            [
                'title' => 'Hướng dẫn chọn game PS5 phù hợp',
                'content' => 'PS5 là một trong những console gaming mạnh mẽ nhất hiện nay. Với đồ họa tuyệt vời và hiệu năng cao, bạn sẽ có trải nghiệm gaming tuyệt vời.

Dưới đây là những tựa game hay nhất cho PS5:

1. **Elden Ring** - RPG hành động với thế giới mở rộng lớn
2. **God of War Ragnarök** - Game hành động phiêu lưu hoành tráng
3. **Final Fantasy XVI** - JRPG kinh điển của Square Enix
4. **Spider-Man 2** - Game siêu anh hùng hành động
5. **Starfield** - Game nhập vai khoa học viễn tưởng từ Bethesda

Chọn game phù hợp với sở thích của bạn và tận hưởng trải nghiệm tốt nhất.',
                'excerpt' => 'PS5 có nhiều tựa game tuyệt vời. Hãy cùng khám phá danh sách những game hot nhất.',
                'author_id' => $user->id,
                'is_published' => true,
                'published_at' => now()
            ]
        );

        Article::firstOrCreate(
            ['slug' => 'top-game-xbox-series-x-2024'],
            [
                'title' => 'Top game Xbox Series X 2024',
                'content' => 'Xbox Series X là console gaming tốt nhất của Microsoft với hiệu năng vượt trội. Dưới đây là những tựa game đáng chơi nhất trên nền tảng này:

**Game Exclusive:**
- Starfield - Game khoa học viễn tưởng hoành tráng
- Forza Motorsport - Racing game đẹp nhất
- Halo Infinite - Game bắn súng huyền thoại

**Game Multiplatform:**
- Cyberpunk 2077 - RPG hành động trong tương lai
- Street Fighter 6 - Game đối kháng cổ điển
- Monster Hunter World - Game action RPG hấp dẫn

Tất cả những tựa game này đều chạy tốt trên Xbox Series X với độ phân giải cao.',
                'excerpt' => 'Khám phá danh sách game hay nhất cho Xbox Series X năm 2024.',
                'author_id' => $user->id,
                'is_published' => true,
                'published_at' => now()->subDay()
            ]
        );

        Article::firstOrCreate(
            ['slug' => 'meo-choi-game-ninja-gaiden-4'],
            [
                'title' => 'Mẹo chơi game Ninja Gaiden 4',
                'content' => 'Ninja Gaiden 4 là một trong những game hành động khó khăn nhất. Dưới đây là những mẹo và chiến lược để bạn có thể chinh phục trò chơi này:

**Tips cơ bản:**
1. Nắm vững các combo tấn công cơ bản
2. Sử dụng ninja tools một cách hiệu quả
3. Rút lui khi máu sắp hết
4. Nên chơi ở difficulty thấp trước

**Boss battles:**
- Nhớ các pattern của boss
- Tìm điểm yếu của từng boss
- Sử dụng ninja items phù hợp
- Không vội vã, hãy chờ lúc thích hợp

Chúc bạn chinh phục được Ninja Gaiden 4!',
                'excerpt' => 'Những mẹo và chiến lược để chiến thắng Ninja Gaiden 4.',
                'author_id' => $user->id,
                'is_published' => true,
                'published_at' => now()->subDays(2)
            ]
        );
    }
}
